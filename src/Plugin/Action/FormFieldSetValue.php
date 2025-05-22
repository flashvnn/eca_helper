<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca_form\Plugin\Action\FormFieldActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Set access to a form field.
 *
 * @Action(
 *   id = "eca_helper_form_field_set_value",
 *   label = @Translation("ECA Helper: Form field set value"),
 *   description = @Translation("Set form field value."),
 *   type = "form"
 * )
 */
class FormFieldSetValue extends FormFieldActionBase {

  use HelperSetValue {
    HelperSetValue::buildConfigurationForm as helperBuildConfigurationForm;
  }

  /**
   * Whether to use form field value filters or not.
   *
   * @var bool
   */
  protected bool $useFilters = FALSE;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->setYamlParser($container->get('eca.service.yaml_parser'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'key' => '',
      'value' => '',
      'value_yaml' => FALSE,
      'array' => FALSE,
      'method' => 'append',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = $this->helperBuildConfigurationForm($form, $form_state);
    if (isset($form['field_name'])) {
      $ex_description = $this->t('Use <em>!!form</em> for the current form instead of form element.');
      $form['field_name']['#description'] = $form['field_name']['#description'] . ' ' . $ex_description;
    }

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
      $key = $this->tokenService->getOrReplace($this->configuration['key']);
      $value = $this->tokenService->getOrReplace($this->configuration['value']);
      $array = $this->tokenService->getOrReplace($this->configuration['array']);
      $method = $this->tokenService->getOrReplace($this->configuration['method']);

      $value_yaml = $this->tokenService->getOrReplace($this->configuration['value_yaml']);
      if ($value_yaml) {
        try {
          $value = $this->yamlParser->parse($value);
        }
        catch (ParseException $e) {
          \Drupal::logger('eca')
            ->error('ECA Helper: Form field set value: Tried parsing value as YAML format, but parsing failed.');
          return;
        }
      }

      if ($key && is_string($key) && mb_strlen($key)) {
        $this->preProcessValue($element, $key, $value);
        $this->setElementValue($element, $key, $value, $array, $method);
      }
    }
  }

}
