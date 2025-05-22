<?php

namespace Drupal\eca_helper\Plugin\Action;

/**
 * Remove HEADER value for response.
 *
 * @Action(
 *   id = "eca_helper_header_remove",
 *   label = @Translation("ECA Helper: Remove headers"),
 *   description = @Translation("Action remove headers value for response."),
 * )
 */
class HeaderRemove extends HeaderSet {

  /**
   * Storage remove headers.
   */
  public static array $headers = [];

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $headers = $this->getHeadersData();
    if (is_string($headers)) {
      $headers = preg_split('/\r\n|\r|\n/', $headers);
      $headers = array_filter($headers);
    }

    if (is_iterable($headers)) {
      self::$headers = $headers;
    }
    else {
      throw new \InvalidArgumentException("Cannot use a non-iterable data value for setting response headers. Data must be resolvable to key-value pairs.");
    }
  }

}
