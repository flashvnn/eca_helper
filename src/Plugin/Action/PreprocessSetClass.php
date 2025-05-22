<?php

namespace Drupal\eca_helper\Plugin\Action;

/**
 * Set variable value from preprocess event.
 *
 * @Action(
 *   id = "eca_helper_preprocess_set_class_value",
 *   label = @Translation("ECA Helper: Preprocess add css class"),
 *   description = @Translation("Set css class with preprocess event.")
 * )
 */
class PreprocessSetClass extends PreprocessSetValue {

  /**
   * {@inheritdoc}
   */
  protected array $hidden = [
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
      'key' => 'attributes.class',
      'array' => TRUE,
    ] + parent::defaultConfiguration();
  }

}
