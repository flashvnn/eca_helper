<?php

namespace Drupal\eca_helper\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * The Preprocess Event.
 *
 * @package Drupal\token_eca_alter\Events
 */
class StatusMessagesEvent extends Event {

  /**
   * Preprocess event.
   */
  const STATUS_MESSAGES = 'eca_helper.status_messages';

  /**
   * The message type.
   */
  protected string $type;

  /**
   * The message content.
   */
  protected string $message;

  /**
   * Handler changed message.
   */
  protected bool $changed = FALSE;

  /**
   * EcaAlterEvent constructor.
   */
  public function __construct($message, $type = 'status') {
    $this->type = $type;
    $this->message = $message;
  }

  /**
   * Get message type.
   *
   * @return string
   *   The message type.
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * Get message content.
   *
   * @return string|null
   *   The message content.
   */
  public function getMessage(): ?string {
    return $this->message;
  }

  /**
   * Set the message.
   *
   * @param string|null $message
   *   The message content.
   */
  public function setMessage(?string $message): void {
    $this->message = $message;
    $this->changed = TRUE;
  }

  /**
   * Check is changed.
   *
   * @return bool
   *   Result value.
   */
  public function isChanged(): bool {
    return $this->changed;
  }

}
