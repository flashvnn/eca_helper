<?php

namespace Drupal\eca_helper\Plugin\ECA\Event;

use Drupal\eca\Plugin\ECA\Event\EventDeriverBase;

/**
 * Deriver for eca_helper preprocess event plugins.
 */
class PreProcessECAEventDeriver extends EventDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function definitions(): array {
    return PreProcessECAEvent::definitions();
  }

}
