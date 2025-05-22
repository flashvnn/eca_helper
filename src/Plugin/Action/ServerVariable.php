<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;

/**
 * Get $_SERVER variables.
 *
 * @Action(
 *   id = "eca_helper_server_variable",
 *   label = @Translation("ECA Helper: Get SERVER Variable"),
 *   description = @Translation("Action get PHP $_SERVER, $_COOKIE, $_SESSION, $_ENV, $_GET, $_POST variable"),
 * )
 */
class ServerVariable extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'variable_type' => 'server',
      'variable' => '',
      'debug' => 0,
      'token_name' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['variable_type'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Variable Type'),
      '#description' => $this->t('Select the variable type'),
      '#default_value' => $this->configuration['variable_type'],
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
      '#options' => [
        'server' => $this->t('$_SERVER'),
        'cookie' => $this->t('$_COOKIE'),
        'session' => $this->t('$_SESSION'),
        'env' => $this->t('$_ENV'),
        'get' => $this->t('$_GET'),
        'post' => $this->t('$_POST'),
      ],
    ];
    $form['variable'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Variable'),
      '#description' => $this->t('The name of server variable need to get data. This support token.'),
      '#default_value' => $this->configuration['variable'],
    ];
    $form['debug'] = [
      '#type' => 'select',
      '#required' => FALSE,
      '#title' => $this->t('Debug'),
      '#description' => $this->t('Enable debug will log all server variable to log messages.'),
      '#default_value' => $this->configuration['debug'],
      '#empty_option' => $this->t('- Select -'),
      '#options' => [
        0 => $this->t('No'),
        1 => $this->t('Yes'),
      ],
    ];
    $form['token_name'] = [
      '#required' => TRUE,
      '#type' => 'textfield',
      '#title' => $this->t('Name of response token'),
      '#default_value' => $this->configuration['token_name'],
      '#description' => $this->t('The response value will be loaded into this specified token.'),
      '#eca_token_reference' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['variable_type'] = $form_state->getValue('variable_type');
    $this->configuration['variable'] = $form_state->getValue('variable');
    $this->configuration['debug'] = $form_state->getValue('debug');
    $this->configuration['token_name'] = $form_state->getValue('token_name');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $variable_type = $this->configuration['variable_type'];
    $server_data = match ($variable_type) {
      default   => $_SERVER,
      'cookie'  => $_COOKIE,
      'session' => $_SESSION,
      'env'     => $_ENV,
      'get'     => $_GET,
      'post'    => $_POST
    };
    $variable = $this->configuration['variable'];
    $debug = $this->configuration['debug'];
    if ($debug) {
      \Drupal::logger('eca_helper')->info('<pre>' . json_encode($server_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</pre>');
    }
    if ($variable && mb_strlen($variable) > 0) {
      $variable = $this->tokenService->getOrReplace($variable);
    }
    if (!empty($this->configuration['token_name'])) {
      $this->tokenService->addTokenData($this->configuration['token_name'], $server_data[$variable] ?? NULL);
    }
  }

}
