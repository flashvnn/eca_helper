<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca_form\Plugin\Action\FormFieldActionBase;

/**
 * Set access to a form field.
 *
 * @Action(
 *   id = "eca_helper_form_dumper",
 *   label = @Translation("ECA Helper: Form dumper"),
 *   description = @Translation("Dump the form data."),
 *   type = "form"
 * )
 */
class FormDumper extends FormFieldActionBase {

  /**
   * Whether to use form field value filters or not.
   *
   * @var bool
   */
  protected bool $useFilters = FALSE;

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $ex_description = $this->t('Use <em>!!form</em> for the current form instead of form element.');
    $form['field_name']['#description'] = $form['field_name']['#description'] . ' ' . $ex_description;
    return $form;
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
      if (function_exists('dump')) {
        dump($element);
      }
      else {
        $this->messenger()
          ->addWarning($this->t('The symfony/var-dumper not found. Please install with command "composer require symfony/var-dumper".'));
      }
    }
  }

}
