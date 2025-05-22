<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca_helper\Event\PreProcessEvent;

/**
 * Set access to a form field.
 *
 * @Action(
 *   id = "eca_helper_preprocess_dumper",
 *   label = @Translation("ECA Helper: Preprocess dumper"),
 *   description = @Translation("Dump the preprocess data."),
 *   type = "form"
 * )
 */
class PreprocessDumper extends ConfigurableActionBase {

  /**
   * Check is dumped .*/
  protected static bool $isDumped = FALSE;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'key' => '',
      'one' => 'yes',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Element key'),
      '#description' => $this->t('The element key to get value for the preprocess. Example plugin_id, attributes.class. Keep empty for dump all variables.'),
      '#default_value' => $this->configuration['key'],
      '#weight' => -49,
      '#eca_token_replacement' => TRUE,
    ];
    $form['one'] = [
      '#type' => 'select',
      '#required' => FALSE,
      '#title' => $this->t('Dump only one'),
      '#description' => $this->t('Only dump one time in case the action called many time.'),
      '#default_value' => 'yes',
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => 'yes',
      '#options' => [
        'yes' => $this->t('Yes'),
        'no' => $this->t('No'),
      ],
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['key'] = $form_state->getValue('key');
    $this->configuration['one'] = $form_state->getValue('one');
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
    if ($key && is_string($key) && mb_strlen($key)) {
      $element = NestedArray::getValue($element, explode('.', $key));
    }
    $one = $this->configuration['one'];

    if (!function_exists('dump')) {
      $this->messenger()
        ->addWarning($this->t('The symfony/var-dumper not found. Please install with command "composer require symfony/var-dumper".'));
      return;
    }
    if ($one === 'yes') {
      if (!self::$isDumped) {
        dump($element);
        self::$isDumped = TRUE;
      }
    }
    else {
      dump($element);
    }
  }

}
