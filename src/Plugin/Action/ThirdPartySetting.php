<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\Core\Config\Entity\ThirdPartySettingsInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;

/**
 * Get/Set ThirdPartySetting of instance.
 *
 * @Action(
 *   id = "eca_helper_third_partyS_setting",
 *   label = @Translation("ECA Helper: Get/Set ThirdPartySetting"),
 *   description = @Translation("Get/Set ThirdPartySetting of instance."),
 * )
 */
class ThirdPartySetting extends ConfigurableActionBase {

  /**
   * The get method.
   */
  const GET = 'get';

  /**
   * The set method.
   */
  const SET = 'set';

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'instance' => NULL,
      'method' => self::GET,
      'module' => '',
      'key' => '',
      'value' => NULL,
      'token_name' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['instance'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Instance'),
      '#description' => $this->t('The name of instance to Get/Set ThirdPartySetting.'),
      '#default_value' => $this->configuration['instance'],
      '#eca_token_reference' => TRUE,
    ];

    $form['method'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Method'),
      '#description' => $this->t('Select method for GET or SET for ThirdPartySetting'),
      '#default_value' => $this->configuration['method'],
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
      '#options' => [
        self::GET => $this->t('GET'),
        self::SET => $this->t('SET'),
      ],
    ];

    $form['module'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Module'),
      '#description' => $this->t('The name of module to Get/Set ThirdPartySetting.'),
      '#default_value' => $this->configuration['module'],
    ];

    $form['key'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Key'),
      '#description' => $this->t('The name of key value to Get/Set ThirdPartySetting.'),
      '#default_value' => $this->configuration['key'],
    ];

    $form['value'] = [
      '#type' => 'textfield',
      '#required' => FALSE,
      '#title' => $this->t('Value'),
      '#description' => $this->t('The value of key to Get/Set ThirdPartySetting.'),
      '#default_value' => $this->configuration['value'],
    ];

    $form['token_name'] = [
      '#required' => FALSE,
      '#type' => 'textfield',
      '#title' => $this->t('Name of response token'),
      '#default_value' => $this->configuration['token_name'],
      '#description' => $this->t('The response value of Get ThirdPartySetting will be loaded into this specified token.'),
      '#eca_token_reference' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['instance'] = $form_state->getValue('instance');
    $this->configuration['method'] = $form_state->getValue('method');
    $this->configuration['module'] = $form_state->getValue('module');
    $this->configuration['key'] = $form_state->getValue('key');
    $this->configuration['value'] = $form_state->getValue('value');
    $this->configuration['token_name'] = $form_state->getValue('token_name');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $instance = $this->configuration['instance'];
    $instance = $this->tokenService->getTokenData($instance);
    if ($instance instanceof ThirdPartySettingsInterface) {
      $method = $this->configuration['method'];
      $module = $this->tokenService->replaceClear($this->configuration['module']);
      $key = $this->tokenService->replaceClear($this->configuration['key']);
      $value = $this->tokenService->replaceClear($this->configuration['value']);
      if ($method === self::SET && $module && $key) {
        $instance->setThirdPartySetting($module, $key, $value);
      }
      if ($method === self::GET && $module && $key) {
        if (!empty($this->configuration['token_name'])) {
          $this->tokenService->addTokenData($this->configuration['token_name'], $instance->getThirdPartySetting($module, $key, $value));
        }
      }
    }
  }

}
