<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca\Service\YamlParser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Quick call custom action for ECA.
 *
 * @Action(
 *   id = "eca_helper_quick_action",
 *   label = @Translation("ECA Helper: Quick Action"),
 *   description = @Translation("Quick call custom action for ECA"),
 * )
 */
class QuickAction extends ConfigurableActionBase {

  /**
   * The YAML parser.
   */
  protected YamlParser $yamlParser;

  /**
   * Store the action info.
   */
  protected array $actions;

  /**
   * Set the YAML parser.
   *
   * @param \Drupal\eca\Service\YamlParser $yaml_parser
   *   The YAML parser.
   */
  public function setYamlParser(YamlParser $yaml_parser): void {
    $this->yamlParser = $yaml_parser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->setYamlParser($container->get('eca.service.yaml_parser'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'action' => '',
      'args' => '',
      'token_name' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $actions = $this->loadQuickActions();

    $options = [];
    foreach ($actions as $id => $action) {
      if (isset($action['label'])) {
        $options[$id] = $action['label'];
      }
    }

    $form['action'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Action'),
      '#description' => $this->t('Select the action'),
      '#default_value' => $this->configuration['action'],
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
      '#options' => $options,
    ];

    $form['args'] = [
      '#type' => 'textarea',
      '#required' => FALSE,
      '#title' => $this->t('Arguments'),
      '#description' => $this->t('The arguments of the action in the YAML format.'),
      '#default_value' => $this->configuration['args'],
    ];

    $form['token_name'] = [
      '#required' => FALSE,
      '#type' => 'textfield',
      '#title' => $this->t('Name of result token'),
      '#default_value' => $this->configuration['token_name'],
      '#description' => $this->t('The result value after call the action will be loaded into this specified token.'),
      '#eca_token_reference' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['action'] = $form_state->getValue('action');
    $this->configuration['args'] = $form_state->getValue('args');
    $this->configuration['token_name'] = $form_state->getValue('token_name');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $actions = $this->loadQuickActions();
    $action = $this->configuration['action'];
    if (!isset($actions[$action])) {
      return;
    }
    $callable = $actions[$action]['callback'] ?? NULL;
    $service = $actions[$action]['service'] ?? NULL;
    if ($service) {
      if (!\Drupal::hasService($service)) {
        return;
      }
      if (!is_callable([\Drupal::service($service), $callable])) {
        return;
      }
    }
    elseif (!is_callable($callable)) {
      return;
    }

    $args = $this->tokenService->getOrReplace($this->configuration['args']);
    try {
      $args = $this->yamlParser->parse($args);
    }
    catch (ParseException $e) {
      $this->logger->error('Tried parsing args parameters as YAML format, but parsing failed.');
      return;
    }
    try {
      if (!is_array($args)) {
        $args = [$args];
      }

      if ($service) {
        $result = call_user_func_array([
          \Drupal::service($service),
          $callable,
        ], $args);
      }
      else {
        $result = call_user_func_array($callable, $args);
      }

      if (!empty($this->configuration['token_name'])) {
        $this->tokenService->addTokenData($this->configuration['token_name'], $result);
      }
    }
    catch (\Throwable $e) {
      $this->logger->error('Call quick action [%name] returned error message: %message', [
        '%name' => $actions[$action]['label'],
        '%message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Load quick actions.
   *
   * @return array
   *   List of quick actions.
   */
  protected function loadQuickActions(): array {
    if (!isset($this->actions)) {
      $this->actions = [];
      try {
        $file = DRUPAL_ROOT . '/sites/eca/EcaActions.php';
        if (file_exists($file)) {
          require_once $file;
        }
        if (function_exists('ECAQuickActions')) {
          $this->actions = call_user_func('ECAQuickActions');
        }
      }
      catch (\Throwable $e) {
        $this->logger->error("ECA Helper load quick actions error: " . $e->getMessage());
      }
    }
    return $this->actions;
  }

}
