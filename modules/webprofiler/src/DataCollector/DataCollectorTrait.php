<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\MethodData;
use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Caster\LinkStub;
use Symfony\Component\VarDumper\Cloner\Stub;

/**
 * Trait with common code for data collectors.
 */
trait DataCollectorTrait {

  /**
   * Return information about a method of a class.
   *
   * @param mixed $class
   *   A class name.
   * @param string $method
   *   A method name.
   *
   * @return \Drupal\webprofiler\MethodData
   *   Array of information about a method of a class.
   */
  public function getMethodData($class, string $method): ?MethodData {
    $class = is_object($class) ? get_class($class) : $class;
    $data = NULL;

    try {
      $reflectedMethod = new \ReflectionMethod($class, $method);

      $data = new MethodData(
        $class,
        $method,
        $reflectedMethod->getFilename(),
        $reflectedMethod->getStartLine()
      );
    }
    catch (\ReflectionException $re) {
      return NULL;
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
   * @return int
   *   A human readable string.
   */
  private function convertToBytes(string $value) {
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

  /**
   * {@inheritDoc}
   */
  protected function getCasters(): array {
    return parent::getCasters() + [
      MethodData::class => function (MethodData $md, array $a, Stub $stub) {
          $a[Caster::PREFIX_DYNAMIC . 'link'] = new LinkStub($md->getClass() . '::' . $md->getMethod(),
            $md->getLine(), 'file://' . $md->getFile());

          return $a;
      },
    ];
  }

}
