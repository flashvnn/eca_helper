<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\eca_form\Plugin\Action\FormFieldActionBase;

/**
 * Set access to a form field.
 *
 * @Action(
 *   id = "eca_helper_form_field_get_value",
 *   label = @Translation("ECA Helper: Form field get value"),
 *   description = @Translation("Get form field value."),
 *   type = "form"
 * )
 */
class FormFieldGetValue extends FormFieldActionBase {

  /**
   * Whether to use form field value filters or not.
   *
   * @var bool
   */
  protected bool $useFilters = FALSE;

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
      '#description' => $this->t('The element key to get value for the form element. Example #title, #attributes.class'),
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
    $form = parent::buildConfigurationForm($form, $form_state);
    $ex_description = $this->t('Use <em>!!form</em> for the current form instead of form element.');
    $form['field_name']['#description'] = $form['field_name']['#description'] . ' ' . $ex_description;
    return $form;
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
  protected function &getTargetElement(): ?array {
    if ($this->configuration['field_name'] === '!!form') {
      $form = &$this->getCurrentForm();
      return $form;
    }
    return parent::getTargetElement();
  }

  /**
   * {@inheritdoc}
   */
  protected function doExecute(): void {
    if ($element = &$this->getTargetElement()) {
      if ($this->configuration['field_name'] !== '!!form') {
        $element = &$this->jumpToFirstFieldChild($element);
      }
      $key = $this->tokenService->getOrReplace($this->configuration['key']);
      if ($key && is_string($key) && mb_strlen($key) && !empty($this->configuration['token_name'])) {
        $result = NestedArray::getValue($element, explode('.', $key));
        $this->tokenService->addTokenData($this->configuration['token_name'], $result);
      }
    }
  }

}
