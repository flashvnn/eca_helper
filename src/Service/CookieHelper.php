<?php

namespace Drupal\eca_helper\Service;

/**
 * Implement cookie helper.
 */
class CookieHelper {

  /**
   * Storage the cookies data.
   */
  protected static array $cookies = [];

  /**
   * Set cookie value.
   *
   * @param string $name
   *   The cookie name.
   * @param string $value
   *   The cookie value.
   * @param int|string|\DateTimeInterface $expire
   *   The expired time.
   * @param string $path
   *   The cookie path.
   */
  public static function setCookie(string $name, string $value, int|string|\DateTimeInterface $expire = 0, string $path = '/'): void {
    self::$cookies[$name] = [
      'value' => $value,
      'expire' => $expire,
      'path' => $path,
    ];
  }

  /**
   * Get all cookies before send to response.
   *
   * @return array
   *   The cookies data.
   */
  public static function getCookies(): array {
    return self::$cookies;
  }

}
