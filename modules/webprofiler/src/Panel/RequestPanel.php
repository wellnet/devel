<?php

namespace Drupal\webprofiler\Panel;

/**
 * Panel to render collected data about the request.
 */
class RequestPanel implements PanelInterface {

  /**
   * {@inheritDoc}
   */
  public function render($token, $name): array {
    /** @var \Symfony\Component\HttpKernel\Profiler\Profiler $profiler */
    $profiler = \Drupal::service('webprofiler.profiler');
    /** @var \Drupal\webprofiler\DataCollector\RequestDataCollector $collector */
    $collector = $profiler->loadProfile($token)->getCollector($name);

    $rows = [];
    foreach ($collector->getRequestQuery()->all() as $key => $el) {
      $row = [];
      $row[] = $key;
      $row[] = $el;
      $rows[] = $row;
    }

    $data = [
      [
        '#type' => 'table',
        '#header' => ['Name', 'Value'],
        '#rows' => $rows,
      ],
      [
        '#type' => 'table',
        '#header' => ['a', 'b'],
        '#rows' => [[1, 2], [3, 4]],
      ],
    ];

    return [
      '#theme' => 'webprofiler_dashboard_panel',
      '#title' => 'Request',
      '#data' => $data,
    ];
  }

}
