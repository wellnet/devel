<?php

namespace Drupal\webprofiler;

/**
 * Generic class Decorator.
 */
class Decorator {

  /**
   * The original object to decorate.
   *
   * @var object
   */
  protected $object;

  /**
   * Class constructor.
   *
   * @param object $object
   *   The original object to decorate.
   */
  public function __construct($object) {
    $this->object = $object;
  }

  /**
   * Return the original (i.e. non decorated) object.
   *
   * @return mixed
   *   The original object.
   */
  public function getOriginalObject() {
    $object = $this->object;
    while ($object instanceof Decorator) {
      $object = $object->getOriginalObject();
    }
    return $object;
  }

  /**
   * Return the object if $method is a PHP callable, FALSE otherwise.
   *
   * @param string $method
   *   The method name.
   * @param bool $checkSelf
   *   TRUE to check this decorator, FALSE to check the original object.
   *
   * @return bool|mixed
   *   The object if $method is a PHP callable, FALSE otherwise.
   */
  public function isCallable($method, $checkSelf = FALSE) {
    // Check the original object.
    $object = $this->getOriginalObject();
    if (is_callable([$object, $method])) {
      return $object;
    }

    // Check Decorators.
    $object = $checkSelf ? $this : $this->object;
    while ($object instanceof Decorator) {
      if (is_callable([$object, $method])) {
        return $object;
      }

      $object = $this->object;
    }

    return FALSE;
  }

  /**
   * Call a method on the original object, with specific arguments.
   *
   * @param string $method
   *   The method to call.
   * @param array $args
   *   The args to pass to the method.
   *
   * @return mixed
   *   The return of the method invocation on the original object.
   *
   * @throws \Exception
   */
  public function __call($method, array $args) {
    if ($object = $this->isCallable($method)) {
      return call_user_func_array([$object, $method], $args);
    }

    throw new \Exception(
      'Undefined method - ' . get_class($this->getOriginalObject()) . '::' . $method
    );
  }

  /**
   * Return the value of a property from the original object.
   *
   * @param string $property
   *   The property name.
   *
   * @return mixed|null
   *   The value of a property from the original object or NULL if the property
   *   doesn't exist on the original object.
   */
  public function __get($property) {
    $object = $this->getOriginalObject();
    if (property_exists($object, $property)) {
      return $object->$property;
    }

    return NULL;
  }

}
