<?php

namespace Drupal\eca_helper\Plugin\Action;

/**
 * Set variable value from preprocess event.
 *
 * @Action(
 *   id = "eca_helper_form_attach_library",
 *   label = @Translation("ECA Helper: Form attach library"),
 *   description = @Translation("Attach library with to a form."),
 *   type = "form"
 * )
 */
class FormAttachLibrary extends FormFieldSetValue {

  /**
   * {@inheritdoc}
   */
  protected array $hidden = [
    'field_name',
    'key',
    'value_yaml',
    'method',
    'array',
  ];

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'field_name' => '!!form',
      'key' => '#attached.library',
      'array' => TRUE,
    ] + parent::defaultConfiguration();
  }

}
