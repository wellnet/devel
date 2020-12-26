<?php

namespace Drupal\webprofiler\DataCollector;

/**
 * Trait with common code for data collectors.
 */
trait DataCollectorTrait {

  /**
   * Return information about a method of a class.
   *
   * @param string $class
   *   A class name.
   * @param string $method
   *   A method name.
   *
   * @return array
   *   Array of information about a method of a class.
   */
  public function getMethodData($class, $method) {
    $class = is_object($class) ? get_class($class) : $class;
    $data = [];

    try {
      $reflectedMethod = new \ReflectionMethod($class, $method);

      $data = [
        'class' => $class,
        'method' => $method,
        'file' => $reflectedMethod->getFilename(),
        'line' => $reflectedMethod->getStartLine(),
      ];
    }
    catch (\ReflectionException $re) {
      // @todo handle the exception.
    }
    finally {
      return $data;
    }
  }

  /**
   * Convert a numeric value to a human readable string.
   *
   * @param string $value
   *   The value to convert.
   *
   * @return int|string
   *   A human readable string.
   */
  private function convertToBytes($value) {
    if ('-1' === $value) {
      return -1;
    }

    $value = strtolower($value);
    $max = strtolower(ltrim($value, '+'));
    if (0 === strpos($max, '0x')) {
      $max = intval($max, 16);
    }
    elseif (0 === strpos($max, '0')) {
      $max = intval($max, 8);
    }
    else {
      $max = intval($max);
    }

    switch (substr($value, -1)) {
      case 't':
        $max *= 1024 * 1024 * 1024 * 1024;
        break;

      case 'g':
        $max *= 1024 * 1024 * 1024;
        break;

      case 'm':
        $max *= 1024 * 1024;
        break;

      case 'k':
        $max *= 1024;
        break;
    }

    return $max;
  }

}
