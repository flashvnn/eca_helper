<?php

namespace Drupal\eca_helper\Plugin\Action;

use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca_helper\Event\PreProcessEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Set variable value from preprocess event.
 *
 * @Action(
 *   id = "eca_helper_preprocess_set_value",
 *   label = @Translation("ECA Helper: Preprocess set value"),
 *   description = @Translation("Set Preprocess variables value.")
 * )
 */
class PreprocessSetValue extends ConfigurableActionBase {

  use HelperSetValue;

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
  public function execute() {
    $event = $this->getEvent();
    if (!$event || !($event instanceof PreProcessEvent)) {
      return;
    }

    $element = &$event->getVariables();
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
        $this->logger
          ->error('ECA Helper: Preprocess set value: Tried parsing value as YAML format, but parsing failed.');
        return;
      }
    }
    if ($key && is_string($key) && mb_strlen($key)) {
      $this->preProcessValue($element, $key, $value);
      $this->setElementValue($element, $key, $value, $array, $method);
    }
  }

}
