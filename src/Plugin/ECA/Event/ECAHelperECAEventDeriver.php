<?php

namespace Drupal\eca_helper\Plugin\ECA\Event;

use Drupal\eca\Plugin\ECA\Event\EventDeriverBase;

/**
 * Deriver for eca_helper event plugins.
 */
class ECAHelperECAEventDeriver extends EventDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function definitions(): array {
    return ECAHelperECAEvent::definitions();
  }

}
