<?php

namespace Drupal\webprofiler\Commands;

use Drupal\webprofiler\DecoratorGeneratorInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for Webprofiler.
 */
class WebprofilerCommands extends DrushCommands {

  /**
   * The decorator generator service.
   *
   * @var \Drupal\webprofiler\DecoratorGeneratorInterface
   */
  private $generator;

  /**
   * WebprofilerCommands constructor.
   *
   * @param \Drupal\webprofiler\DecoratorGeneratorInterface $generator
   *   The decorator generator service.
   */
  public function __construct(DecoratorGeneratorInterface $generator) {
    parent::__construct();

    $this->generator = $generator;
  }

  /**
   * Generate decorators for ConfigEntityStorageInterface.
   *
   * @command webprofiler:generateDecorators
   * @aliases wp-decorators
   */
  public function generateDecorators() {
    try {
      $this->generator->generate();
    }
    catch (\Exception $e) {
      $this->writeln($e->getMessage());
    }
  }

}
