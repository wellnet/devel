<?php

namespace Drupal\webprofiler\Commands;

use Drupal\webprofiler\DecoratorGeneratorInterface;
use Drush\Commands\DrushCommands;

/**
 * Class WebprofilerCommands.
 */
class WebprofilerCommands extends DrushCommands {

  /**
   * @var \Drupal\webprofiler\DecoratorGeneratorInterface
   */
  private $generator;

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
    } catch (\Exception $e) {
      $this->writeln($e->getMessage());
    }
  }

}
