<?php

namespace Drupal\eca_helper\Service;

use Drupal\Component\Utility\Html;
use Drupal\eca_helper\Plugin\Action\HeaderFooterTag;

/**
 * Implement page attachment alter.
 */
class PageAttachmentAlter {

  /**
   * Page attachments alter.
   *
   * @param array $attachments
   *   The current attachments.
   * @param string $position
   *   The alter position.
   */
  public function alter(array &$attachments, string $position): void {
    if (!isset(HeaderFooterTag::$data[$position])) {
      return;
    }
    $tags = HeaderFooterTag::$data[$position];
    foreach ($tags as $type => $content) {
      if (is_string($content)) {
        $content = [$content];
      }
      foreach ((array) $content as $item) {
        $html_tag = NULL;
        if ($type === 'markup') {
          $html_tag = [
            '#type' => 'inline_template',
            '#template' => $item,
          ];
        }
        if ($type === 'script' || $type === 'style') {
          $html_tag = [
            '#type' => 'html_tag',
            '#tag' => $type,
            '#value' => $item,
          ];
        }
        if (!$html_tag) {
          continue;
        }
        if ($position === 'header') {
          $attachments['#attached']['html_head'][] = [
            $html_tag,
            Html::getUniqueId('eca-helper-head'),
          ];
        }
        else {
          $attachments[Html::getUniqueId('eca-helper-head')] = $html_tag;
        }
      }
    }
  }

}
