<?php

namespace Drupal\webprofiler;

/**
 * Value object to store some method data from reflection.
 */
class MethodData {

  /**
   * The method class.
   *
   * @var string
   */
  private string $class;

  /**
   * The method name.
   *
   * @var string
   */
  private string $method;

  /**
   * The method file.
   *
   * @var string
   */
  private string $file;

  /**
   * The method line in file.
   *
   * @var string
   */
  private string $line;

  /**
   * MethodData constructor.
   *
   * @param string $class
   *   The method class.
   * @param string $method
   *   The method name.
   * @param string $file
   *   The method file.
   * @param string $line
   *   The method line in file.
   */
  public function __construct(string $class, string $method, string $file, string $line) {
    $this->class = $class;
    $this->method = $method;
    $this->file = $file;
    $this->line = $line;
  }

  /**
   * Return the method class.
   *
   * @return string
   *   The method class.
   */
  public function getClass(): string {
    return $this->class;
  }

  /**
   * Return the method name.
   *
   * @return string
   *   The method name.
   */
  public function getMethod(): string {
    return $this->method;
  }

  /**
   * Return the method file.
   *
   * @return string
   *   The method file.
   */
  public function getFile(): string {
    return $this->file;
  }

  /**
   * Return the method line in file.
   *
   * @return string
   *   The method line in file.
   */
  public function getLine(): string {
    return $this->line;
  }

}
