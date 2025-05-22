<?php

namespace Drupal\eca_helper\Plugin\ECA\Event;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Theme\Registry;
use Drupal\eca\Entity\Objects\EcaEvent;
use Drupal\eca\Event\Tag;
use Drupal\eca\Plugin\ECA\Event\EventBase;
use Drupal\eca_helper\Event\PreProcessEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Plugin implementation of the ECA Events for config.
 *
 * @EcaEvent(
 *   id = "eca_helper_preprocess_hook",
 *   deriver = "Drupal\eca_helper\Plugin\ECA\Event\PreProcessECAEventDeriver"
 * )
 */
class PreProcessECAEvent extends EventBase {

  /**
   * The theme registry.
   */
  protected ?Registry $themeRegistry;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->themeRegistry = $container->get('theme.registry');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return ['type' => '-any-'] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public static function definitions(): array {
    return [
      'preprocess' => [
        'label' => 'ECA Helper: Preprocess',
        'event_name' => PreProcessEvent::PREPROCESS,
        'event_class' => PreProcessEvent::class,
        'tags' => Tag::WRITE | Tag::READ,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    if ($this->eventClass() === PreProcessEvent::class) {
      $markup = $this->t('This event provides tokens: "[hook]" the preprocess hook id to identify.');
      $form['help'] = [
        '#type' => 'markup',
        '#markup' => $markup,
        '#weight' => 10,
        '#description' => $markup,
      ];
      $form['type'] = [
        '#type' => 'select',
        '#title' => $this->t('Preprocess hook'),
        '#options' => $this->getPreprocessHook(),
        '#default_value' => $this->configuration['type'],
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);
    if ($this->eventClass() === PreProcessEvent::class) {
      $this->configuration['type'] = $form_state->getValue('type');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function generateWildcard(string $eca_config_id, EcaEvent $ecaEvent): string {
    $plugin = $ecaEvent->getPlugin();
    if ($plugin->getderivativeId() === 'preprocess') {
      $type = $ecaEvent->getConfiguration()['type'] ?? '-any-';
      if ($type === '-any-') {
        return '*';
      }
      return 'hook::' . $type;
    }
    return '*';
  }

  /**
   * {@inheritdoc}
   */
  public static function appliesForWildcard(Event $event, string $event_name, string $wildcard): bool {
    if ($event instanceof PreProcessEvent) {
      if ($wildcard === '*') {
        return TRUE;
      }
      return $wildcard === 'hook::' . $event->getHook();
    }
    return parent::appliesForWildcard($event, $event_name, $wildcard);
  }

  /**
   * Retrieves an array of preprocess hooks available in the theme registry.
   *
   * This method fetches all keys from the theme registry and returns them as an
   * array. The output includes a default `- any -` option, translated for
   * localization, followed by theme registry hooks as both keys and values.
   *
   * @return array
   *   An associative array where:
   *   - The key `-any-` maps to the translated string `- any -`.
   *   - Remaining keys and values are derived from the theme registry hooks.
   */
  protected function getPreprocessHook(): array {
    $hook = array_keys($this->themeRegistry->get());
    sort($hook);
    return ['-any-' => $this->t('- any -')] + array_combine($hook, $hook);
  }

}
