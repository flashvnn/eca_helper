<?php

namespace Drupal\eca_helper\Plugin\Action;

/**
 * Set variable value from preprocess event.
 *
 * @Action(
 *   id = "eca_helper_form_add_class",
 *   label = @Translation("ECA Helper: Form add css class"),
 *   description = @Translation("Add form css class."),
 *   type = "form"
 * )
 */
class FormAddClass extends FormFieldSetValue {

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
      'key' => '#attributes.class',
      'array' => TRUE,
    ] + parent::defaultConfiguration();
  }

}
