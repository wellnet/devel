<?php

namespace Drupal\webprofiler\Entity;

use Drupal\webprofiler\Decorator;

/**
 * Decorator for services that manage entities.
 */
class EntityDecorator extends Decorator {

  /**
   * Entities managed by services decorated with this decorator.
   *
   * @var array
   */
  protected $entities;

  /**
   * Return the entities managed by services decorated with this decorator.
   *
   * @return mixed
   *   The entities managed by services decorated with this decorator.
   */
  public function getEntities() {
    return $this->entities;
  }

}
