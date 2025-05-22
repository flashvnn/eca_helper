<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca_helper\Service\CookieHelper;

/**
 * Get $_SERVER variables.
 *
 * @Action(
 *   id = "eca_helper_cookie_set",
 *   label = @Translation("ECA Helper: Set cookie"),
 *   description = @Translation("Action cookie value for response."),
 * )
 */
class CookieSet extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'name' => 'cookie_name',
      'value' => '',
      'expire' => 0,
      'path' => '/',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['name'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Cookie name'),
      '#description' => $this->t('The cookie name'),
      '#default_value' => $this->configuration['name'],
    ];

    $form['value'] = [
      '#type' => 'textfield',
      '#required' => FALSE,
      '#title' => $this->t('Cookie value'),
      '#description' => $this->t('The cookie value.'),
      '#default_value' => $this->configuration['value'],
    ];

    $form['expire'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Expiration time'),
      '#description' => $this->t('The cookie expiration time. Value can timespan or date string. Use 0 for never expired.'),
      '#default_value' => $this->configuration['expire'],
    ];

    $form['path'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Cookie path'),
      '#description' => $this->t('The cookie path.'),
      '#default_value' => $this->configuration['path'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['name'] = $form_state->getValue('name');
    $this->configuration['value'] = $form_state->getValue('value');
    $this->configuration['expire'] = $form_state->getValue('expire');
    $this->configuration['path'] = $form_state->getValue('path');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $values = [];
    foreach ($this->configuration as $id => $value) {
      $values[$id] = $this->tokenService->getOrReplace($this->configuration[$id]);
    }
    if (strlen($values['name']) <= 0) {
      return;
    }

    if ($values['expire'] && is_numeric($values['expire'])) {
      $values['expire'] = $values['expire'] + 0;
    }

    CookieHelper::setCookie($values['name'], $values['value'], $values['expire'] ?? 0, $values['path'] ?? '/');
  }

}
