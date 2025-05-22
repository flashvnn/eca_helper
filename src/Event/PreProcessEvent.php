<?php

namespace Drupal\eca_helper\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * The Preprocess Event.
 *
 * @package Drupal\token_eca_alter\Events
 */
class PreProcessEvent extends Event {

  /**
   * Preprocess event.
   */
  const PREPROCESS = 'eca_helper.preprocess';

  /**
   * The hook id.
   */
  protected string $hook;

  /**
   * The hook variables.
   */
  protected array $variables;

  /**
   * EcaAlterEvent constructor.
   */
  public function __construct(string $hook, array &$variables) {
    $this->hook = $hook;
    $this->variables = &$variables;
  }

  /**
   * Get preprocess hook id.
   *
   * @return string
   *   The hook id.
   */
  public function getHook(): string {
    return $this->hook;
  }

  /**
   * Get hook variables.
   *
   * @return array
   *   The hook variables.
   */
  public function &getVariables(): array {
    return $this->variables;
  }

}
