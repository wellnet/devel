<?php

namespace Drupal\webprofiler;

/**
 * Interface DecoratorGeneratorInterface.
 */
interface DecoratorGeneratorInterface {

  /**
   * Generates Entity Storage decorators.
   *
   * @throws \Exception
   */
  public function generate();

  /**
   * List available decorators.
   *
   * @return array
   */
  public function getDecorators(): array;

}
