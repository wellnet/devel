<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\Panel\PanelInterface;

/**
 * Interface for DataCollector classes.
 */
interface DrupalDataCollectorInterface {

  /**
   * Return the class used to render data for this data collector.
   *
   * @return \Drupal\webprofiler\Panel\PanelInterface
   *   A class that can render this data collector.
   */
  public function getPanel(): PanelInterface;

}
