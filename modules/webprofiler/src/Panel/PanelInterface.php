<?php

namespace Drupal\webprofiler\Panel;

/**
 * Interface for dashboard panels.
 */
interface PanelInterface {

  /**
   * Render a panel.
   *
   * @param string $token
   *   A profile token.
   * @param string $name
   *   The panel name.
   *
   * @return array
   *   A render array for this panel.
   */
  public function render($token, $name): array;

}
