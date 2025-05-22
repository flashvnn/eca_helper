<?php

namespace Drupal\eca_helper\Plugin\Action;

/**
 * Helper for process data and array.
 */
trait Helper {

  /**
   * Check valid json response.
   *
   * @param mixed $value
   *   The input string.
   *
   * @return bool
   *   Check result.
   */
  public function isJson(mixed $value): bool {
    if (!is_string($value)) {
      return FALSE;
    }

    if (function_exists('json_validate')) {
      return json_validate($value, 512);
    }

    try {
      json_decode($value, TRUE, 512, JSON_THROW_ON_ERROR);
    }
    catch (\JsonException) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Remove / unset an item from an array or object using "dot" notation.
   *
   * @param mixed $target
   *   The target object or array.
   * @param string|array|int|null $key
   *   The key.
   *
   * @return mixed
   *   Processed data.
   */
  public function dataForget(&$target, $key): mixed {
    $segments = is_array($key) ? $key : explode('.', $key);

    if (($segment = array_shift($segments)) === '*' && $this->arrAccessible($target)) {
      if ($segments) {
        foreach ($target as &$inner) {
          $this->dataForget($inner, $segments);
        }
      }
    }
    elseif ($this->arrAccessible($target)) {
      if ($segments && $this->arrExists($target, $segment)) {
        $this->dataForget($target[$segment], $segments);
      }
      else {
        $this->arrForget($target, $segment);
      }
    }
    elseif (is_object($target)) {
      if ($segments && isset($target->{$segment})) {
        $this->dataForget($target->{$segment}, $segments);
      }
      elseif (isset($target->{$segment})) {
        unset($target->{$segment});
      }
    }

    return $target;
  }

  /**
   * Determine whether the given value is array accessible.
   *
   * @param mixed $value
   *   The check value.
   *
   * @return bool
   *   The check result.
   */
  public function arrAccessible(mixed $value): bool {
    return is_array($value) || $value instanceof \ArrayAccess;
  }

  /**
   * Remove one or many array items from a given array using "dot" notation.
   *
   * @param array $array
   *   The array.
   * @param float|int|array|string $keys
   *   The key.
   */
  public function arrForget(array &$array, float|int|array|string $keys): void {
    $original = &$array;

    $keys = (array) $keys;

    if (count($keys) === 0) {
      return;
    }

    foreach ($keys as $key) {
      // If the exact key exists in the top-level, remove it.
      if ($this->arrExists($array, $key)) {
        unset($array[$key]);

        continue;
      }

      $parts = explode('.', $key);

      // Clean up before each pass.
      $array = &$original;

      while (count($parts) > 1) {
        $part = array_shift($parts);

        if (isset($array[$part]) && $this->arrAccessible($array[$part])) {
          $array = &$array[$part];
        }
        else {
          continue 2;
        }
      }

      unset($array[array_shift($parts)]);
    }
  }

  /**
   * Determine if the given key exists in the provided array.
   *
   * @param \ArrayAccess|array $array
   *   The array.
   * @param int|string|float $key
   *   The key.
   *
   * @return bool
   *   The result.
   */
  public function arrExists(\ArrayAccess|array $array, int|string|float $key): bool {
    if ($array instanceof \ArrayAccess) {
      return $array->offsetExists($key);
    }

    if (is_float($key)) {
      $key = (string) $key;
    }

    return array_key_exists($key, $array);
  }

}
