<?php

namespace Drupal\eca_helper\Plugin\Action;

/**
 * Set variable value from preprocess event.
 *
 * @Action(
 *   id = "eca_helper_preprocess_remove_item",
 *   label = @Translation("ECA Helper: Preprocess remove item"),
 *   description = @Translation("Remove item by key with preprocess.")
 * )
 */
class PreprocessRemoveItem extends PreprocessSetValue {

  use Helper;

  /**
   * {@inheritdoc}
   */
  protected array $hidden = [
    'value_yaml',
    'method',
    'array',
    'value',
  ];

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'key' => '',
      'array' => TRUE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  protected function getDescription(string $key): ?string {
    if ($key === 'key') {
      return $this->t('The element key to remove value for the form element. Example #title, #attributes.class');
    }
    return parent::getDescription($key);
  }

  /**
   * {@inheritdoc}
   */
  protected function setElementValue(array &$element, string $key, mixed $value, bool $is_array = FALSE, string $method = 'append'): void {
    $this->dataForget($element, $key);
  }

}
