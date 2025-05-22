<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca_helper\Event\StatusMessagesEvent;

/**
 * Alter the message content.
 *
 * @Action(
 *   id = "eca_helper_status_messages_alter",
 *   label = @Translation("ECA Helper: Status Messages Alter"),
 *   description = @Translation("Action allow alter status messages content."),
 * )
 */
class StatusMessagesAlter extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'value' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['value'] = [
      '#type' => 'textfield',
      '#required' => FALSE,
      '#title' => $this->t('Message value'),
      '#description' => $this->t('Input the message value. Leave blank to use the remove the message.'),
      '#default_value' => $this->configuration['value'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['value'] = $form_state->getValue('value');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $value = $this->tokenService->getOrReplace($this->configuration['value']);
    if ($this->event instanceof StatusMessagesEvent) {
      $this->event->setMessage($value);
    }
  }

}
