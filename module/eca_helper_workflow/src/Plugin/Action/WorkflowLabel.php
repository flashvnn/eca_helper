<?php

namespace Drupal\eca_helper_workflow\Plugin\Action;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca\Plugin\DataType\DataTransferObject;
use Drupal\eca\Service\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Action get workflow label.
 *
 * @Action(
 *   id = "eca_helper_workflow_label",
 *   label = @Translation("ECA Helper Workflow: Get Label"),
 *   description = @Translation("Get workflow label"),
 *   type = "entity"
 * )
 */
class WorkflowLabel extends ConfigurableActionBase {

  /**
   * The moderation information service.
   */
  protected ModerationInformationInterface $moderationInformation;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Token $token_service, ModerationInformationInterface $moderation_information) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $token_service);
    $this->moderationInformation = $moderation_information;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $instance */
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->moderationInformation = $container->get('content_moderation.moderation_information');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'workflow_state' => '',
      'token_name' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['workflow_state'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The workflow state id'),
      '#description' => $this->t('The workflow state id of entity.'),
      '#default_value' => $this->configuration['workflow_state'],
      '#weight' => -20,
    ];

    $form['token_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of token'),
      '#default_value' => $this->configuration['token_name'],
      '#description' => $this->t('The field value will be loaded into this specified token.'),
      '#weight' => -10,
      '#eca_token_reference' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['workflow_state'] = $form_state->getValue('workflow_state');
    $this->configuration['token_name'] = $form_state->getValue('token_name');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Get token value.
   *
   * @param string $text
   *   The input text.
   * @param bool $reset
   *   Is reset if value is array.
   *
   * @return mixed
   *   The result.
   */
  public function getTokenValue(string $text, bool $reset = FALSE): mixed {
    $value = $this->tokenService->getOrReplace($text);
    if ($value instanceof DataTransferObject) {
      $value = $value->getValue();
    }
    if ($reset) {
      if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
        $value = (string) $value;
      }

      if (is_array($value)) {
        $value = reset($value);
      }
    }

    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!($entity instanceof EntityInterface)) {
      return;
    }
    $workflow_state = $this->getTokenValue($this->configuration['workflow_state'], TRUE);
    $moderation_information_service = $this->moderationInformation;
    $label = '';
    if ($workflow = $moderation_information_service->getWorkflowForEntity($entity)) {
      $label = $workflow->getTypePlugin()->getState($workflow_state)?->label();
    }
    $this->tokenService->addTokenData($this->configuration['token_name'], $label ?? '');
  }

}
