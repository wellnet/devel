<?php

namespace Drupal\webprofiler\Panel;

/**
 * Panel to render collected data about drupal.
 *
 * @package Drupal\webprofiler\Panel
 */
class DrupalPanel extends PanelBase implements PanelInterface {

  /**
   * {@inheritDoc}
   */
  public function render($token, $name): array {
    /** @var \Symfony\Component\HttpKernel\Profiler\Profiler $profiler */
    $profiler = \Drupal::service('webprofiler.profiler');
    /** @var \Drupal\webprofiler\DataCollector\DrupalDataCollector $collector */
    $collector = $profiler->loadProfile($token)->getCollector($name);

    // TODO: complete.
    return [
      '#theme' => 'webprofiler_dashboard_panel',
      '#title' => $this->t('Drupal'),
      '#data' => [
        '#theme' => 'webprofiler_dashboard_table',
        '#title' => 'Drupal',
        '#data' => [
          '#type' => 'table',
          '#header' => [
            $this->t('Label'),
            $this->t('Value'),
          ],
          '#rows' => [
            ['Drupal', $collector->getVersion()],
            ['Profile', $collector->getProfile()],
          ],
          '#attributes' => [
            'class' => [
              'webprofiler__table',
            ],
          ],
          '#sticky' => TRUE,
        ],
      ],
    ];
  }

}