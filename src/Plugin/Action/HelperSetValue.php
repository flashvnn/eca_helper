<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Service\YamlParser;

/**
 * Helper set value trait.
 */
trait HelperSetValue {

  /**
   * The YAML parser.
   */
  protected YamlParser $yamlParser;

  /**
   * Define hidden elements.
   */
  protected array $hidden = [];

  /**
   * Define override description.
   */
  protected array $description = [];

  /**
   * Set the YAML parser.
   *
   * @param \Drupal\eca\Service\YamlParser $yaml_parser
   *   The YAML parser.
   */
  public function setYamlParser(YamlParser $yaml_parser): void {
    $this->yamlParser = $yaml_parser;
  }

  /**
   * Get override description.
   *
   * @param string $key
   *   The description key.
   *
   * @return string|null
   *   Result.
   */
  protected function getDescription(string $key): ?string {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    if (in_array('field_name', $this->hidden) && isset($form['field_name'])) {
      unset($form['field_name']);
    }

    if (!in_array('key', $this->hidden)) {
      $form['key'] = [
        '#type' => 'textfield',
        '#reqruired' => TRUE,
        '#title' => $this->t('Element key'),
        '#description' => $this->getDescription('key') ?? $this->t('The element key to set value for the form element. Example #title, #attributes.class'),
        '#default_value' => $this->configuration['key'],
        '#weight' => -49,
        '#eca_token_replacement' => TRUE,
      ];
    }

    if (!in_array('value', $this->hidden)) {
      $form['value'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Value'),
        '#reqruired' => TRUE,
        '#description' => $this->getDescription('value') ?? $this->t('The element value.'),
        '#default_value' => $this->configuration['value'],
        '#weight' => -48,
        '#eca_token_replacement' => TRUE,
      ];
    }

    if (!in_array('value_yaml', $this->hidden)) {
      $form['value_yaml'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Value in YAML'),
        '#description' => $this->getDescription('value_yaml') ?? $this->t('Value in the YAML format.'),
        '#required' => FALSE,
        '#return_value' => 1,
        '#weight' => -48,
        '#default_value' => $this->configuration['value_yaml'],
      ];
    }

    if (!in_array('array', $this->hidden)) {
      $form['array'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Set array value'),
        '#description' => $this->getDescription('array') ?? $this->t('Set or append data to array value.'),
        '#required' => FALSE,
        '#return_value' => 1,
        '#weight' => -47,
        '#default_value' => $this->configuration['array'],
      ];
    }

    if (!in_array('method', $this->hidden)) {
      $form['method'] = [
        '#type' => 'select',
        '#title' => $this->t('Method'),
        '#default_value' => $this->configuration['method'],
        '#description' => $this->getDescription('array') ?? $this->t('The method to set value when use Set array value.'),
        '#weight' => -40,
        '#options' => [
          'set:clear' => $this->t('Set and clear previous value'),
          'set:clear_array' => $this->t('Set current value array and clear previous value'),
          'set:empty' => $this->t('Set only when empty'),
          'append' => $this->t('Append to exist value'),
          'remove' => $this->t('Remove value instead of adding it'),
        ],
      ];
    }

    return $form;
  }

  /**
   * Set array render value.
   *
   * @param array $element
   *   The render element.
   * @param string $key
   *   The element key.
   * @param mixed $value
   *   The value.
   * @param bool $is_array
   *   The is array identify.
   * @param string $method
   *   The method when use array.
   */
  protected function setElementValue(array &$element, string $key, mixed $value, bool $is_array = FALSE, string $method = 'append'): void {
    if ($is_array) {
      $array_value = NestedArray::getValue($element, explode(".", $key)) ?? [];
      if (!is_array($array_value)) {
        $array_value = [$array_value];
      }
      if ($method === 'set:clear') {
        $array_value = [$value];
      }
      if ($method === 'set:clear_array' && is_array($value)) {
        $array_value = $value;
      }
      if ($method === 'set:empty' && empty($array_value)) {
        $array_value = [$value];
      }
      if ($method === 'append') {
        $array_value[] = $value;
      }
      if ($method === 'remove') {
        $array_value = array_values(array_diff($array_value, is_array($value) ? $value : [$value]));
      }
      NestedArray::setValue($element, explode(".", $key), $array_value);
      return;
    }
    NestedArray::setValue($element, explode(".", $key), $value);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    if (!in_array('key', $this->hidden)) {
      $this->configuration['key'] = $form_state->getValue('key');
    }
    if (!in_array('value', $this->hidden)) {
      $this->configuration['value'] = $form_state->getValue('value');
    }
    if (!in_array('value_yaml', $this->hidden)) {
      $this->configuration['value_yaml'] = $form_state->getValue('value_yaml');
    }
    if (!in_array('array', $this->hidden)) {
      $this->configuration['array'] = $form_state->getValue('array');
    }
    if (!in_array('method', $this->hidden)) {
      $this->configuration['method'] = $form_state->getValue('method');
    }
    if (in_array('field_name', $this->hidden)) {
      $form_state->setValue('field_name', $this->configuration['field_name']);
    }

    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Preprocess value before set to element.
   */
  protected function preProcessValue(mixed $element, string &$key, mixed &$value): void {
  }

}
