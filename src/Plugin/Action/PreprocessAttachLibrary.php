<?php

namespace Drupal\eca_helper\Plugin\Action;

/**
 * Set variable value from preprocess event.
 *
 * @Action(
 *   id = "eca_helper_preprocess_attach_library",
 *   label = @Translation("ECA Helper: Preprocess attach library"),
 *   description = @Translation("Attach library with preprocess event.")
 * )
 */
class PreprocessAttachLibrary extends PreprocessSetValue {

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
      'key' => '#attached.library',
      'array' => TRUE,
    ] + parent::defaultConfiguration();
  }

}
