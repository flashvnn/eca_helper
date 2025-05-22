<?php

namespace Drupal\eca_helper\Plugin\ECA\Event;

use Drupal\eca\Attributes\Token;
use Drupal\eca\Event\Tag;
use Drupal\eca\Plugin\ECA\Event\EventBase;
use Drupal\eca_helper\Event\StatusMessagesEvent;

/**
 * Plugin implementation of the ECA Helper Events.
 *
 * @EcaEvent(
 *   id = "eca_helper",
 *   deriver = "Drupal\eca_helper\Plugin\ECA\Event\ECAHelperECAEventDeriver"
 * )
 */
class ECAHelperECAEvent extends EventBase {

  /**
   * {@inheritdoc}
   */
  public static function definitions(): array {
    return [
      'status_messages' => [
        'label' => 'ECA Helper: Status Messages',
        'event_name' => StatusMessagesEvent::STATUS_MESSAGES,
        'event_class' => StatusMessagesEvent::class,
        'tags' => Tag::WRITE | Tag::READ,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  #[Token(
    name: 'event',
    description: 'The status messages event. Support [event:type], [event:message].',
    properties: [
      new Token(name: 'type', description: 'The message type.'),
      new Token(name: 'message', description: 'The message content.'),
    ],
  )]
  protected function buildEventData(): array {
    $event = $this->event;
    $data = [];
    if ($event instanceof StatusMessagesEvent) {
      $data += [
        'type' => $event->getType(),
        'message' => $event->getMessage(),
      ];
    }
    $data += parent::buildEventData();
    return $data;
  }

}
