<?php

namespace Drupal\webprofiler\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the report page.
 */
class ReportController extends ControllerBase {

  /**
   * The Profiler service.
   *
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  private $profiler;

  /**
   * The Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  private $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);

    $instance->profiler = $container->get('webprofiler.profiler');
    $instance->dateFormatter = $container->get('date.formatter');

    return $instance;
  }

  /**
   * Generates the list page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A request object.
   *
   * @return array
   *   A render array for the profile list table.
   */
  public function list(Request $request) {
    $limit = $request->get('limit', 10);
    $this->profiler->disable();

    $ip = $request->query->get('ip');
    $method = $request->query->get('method');
    $url = $request->query->get('url');

    $profiles = $this->profiler->find($ip, $url, $limit, $method, '', '');

    $rows = [];
    if (count($profiles)) {
      foreach ($profiles as $profile) {
        $row = [];
        $row[] = Link::fromTextAndUrl($profile['token'], new Url('webprofiler.dashboard', ['token' => $profile['token']]))->toString();
        $row[] = $profile['ip'];
        $row[] = $profile['method'];
        $row[] = $profile['url'];
        $row[] = $this->dateFormatter->format($profile['time']);

        $rows[] = $row;
      }
    }
    else {
      $rows[] = [
        [
          'data' => $this->t('No profiles found'),
          'colspan' => 6,
        ],
      ];
    }

    $build = [];

    $build['table'] = [
      '#type' => 'table',
      '#rows' => $rows,
      '#header' => [
        $this->t('Token'),
        [
          'data' => $this->t('Ip'),
          'class' => [RESPONSIVE_PRIORITY_LOW],
        ],
        [
          'data' => $this->t('Method'),
          'class' => [RESPONSIVE_PRIORITY_LOW],
        ],
        $this->t('Url'),
        [
          'data' => $this->t('Time'),
          'class' => [RESPONSIVE_PRIORITY_MEDIUM],
        ],
      ],
      '#sticky' => TRUE,
    ];

    return $build;
  }

}
