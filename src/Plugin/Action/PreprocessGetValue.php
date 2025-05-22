<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca_helper\Event\PreProcessEvent;

/**
 * Get value from preprocess hook.
 *
 * @Action(
 *   id = "eca_helper_preprocess_get_value",
 *   label = @Translation("ECA Helper: Preprocess get value"),
 *   description = @Translation("Get element value from preprocess event.")
 * )
 */
class PreprocessGetValue extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'key' => '',
      'token_name' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Element key'),
      '#description' => $this->t('The element key to get value for the preprocess. Example #title, #attributes.class'),
      '#default_value' => $this->configuration['key'],
      '#weight' => -49,
      '#eca_token_replacement' => TRUE,
    ];
    $form['token_name'] = [
      '#required' => TRUE,
      '#type' => 'textfield',
      '#title' => $this->t('Name of response token'),
      '#default_value' => $this->configuration['token_name'],
      '#description' => $this->t('The response value will be loaded into this specified token.'),
      '#eca_token_reference' => TRUE,
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['key'] = $form_state->getValue('key');
    $this->configuration['token_name'] = $form_state->getValue('token_name');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    $event = $this->getEvent();
    if (!$event || !($event instanceof PreProcessEvent)) {
      return;
    }
    $element = $event->getVariables();
    $key = $this->tokenService->getOrReplace($this->configuration['key']);
    if ($key && is_string($key) && mb_strlen($key) && !empty($this->configuration['token_name'])) {
      $result = NestedArray::getValue($element, explode('.', $key));
      $this->tokenService->addTokenData($this->configuration['token_name'], $result);
    }
  }

}
