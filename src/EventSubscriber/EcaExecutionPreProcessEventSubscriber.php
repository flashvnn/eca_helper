<?php

namespace Drupal\eca_helper\EventSubscriber;

use Drupal\eca\EcaEvents;
use Drupal\eca\Event\BeforeInitialExecutionEvent;
use Drupal\eca\EventSubscriber\EcaExecutionSubscriberBase;
use Drupal\eca_helper\Event\PreProcessEvent;

/**
 * Adds the configuration to the Token service when executing ECA logic.
 */
class EcaExecutionPreProcessEventSubscriber extends EcaExecutionSubscriberBase {

  /**
   * Subscriber method before initial execution.
   *
   * @param \Drupal\eca\Event\BeforeInitialExecutionEvent $before_event
   *   The according event.
   */
  public function onBeforeInitialExecution(BeforeInitialExecutionEvent $before_event): void {
    $event = $before_event->getEvent();
    if ($event instanceof PreProcessEvent) {
      $this->tokenService->addTokenData('hook', $event->getHook());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = [];
    $events[EcaEvents::BEFORE_INITIAL_EXECUTION][] = ['onBeforeInitialExecution'];
    return $events;
  }

}
