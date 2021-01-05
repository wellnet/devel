<?php

namespace Drupal\webprofiler\Panel;

use Drupal\webprofiler\DataCollector\RequestDataCollector;

/**
 * Panel to render collected data about the request.
 */
class RequestPanel extends PanelBase implements PanelInterface {

  /**
   * {@inheritDoc}
   */
  public function render($token, $name): array {
    /** @var \Symfony\Component\HttpKernel\Profiler\Profiler $profiler */
    $profiler = \Drupal::service('webprofiler.profiler');
    /** @var \Drupal\webprofiler\DataCollector\RequestDataCollector $collector */
    $collector = $profiler->loadProfile($token)->getCollector($name);

    $data = array_merge(
      $this->renderTable(
        $collector->getRequestQuery()->all(), 'GET parameters'),
      $this->renderTable(
        $collector->getRequestRequest()->all(), 'POST parameters'),
      $this->renderTable(
        $collector->getRequestAttributes()->all(), 'Request attributes'),
      $this->renderAccessChecks(
        $collector->getAccessChecks()->all(), 'Access check'),
      $this->renderTable(
        $collector->getRequestCookies()->all(), 'Cookies'),
      $this->renderTable(
        $collector->getSessionMetadata(), 'Session Metadata'),
      $this->renderTable(
        $collector->getSessionAttributes(), 'Session Attributes'),
      $this->renderTable(
        $collector->getRequestHeaders()->all(), 'Request headers'),
      $this->renderContent(
        $collector->getContent(), 'Raw content'),
      $this->renderTable(
        $collector->getRequestServer()->all(), 'Server Parameters'),
      $this->renderTable(
        $collector->getResponseHeaders()->all(), 'Response headers')
    );

    return [
      '#theme' => 'webprofiler_dashboard_panel',
      '#title' => $this->t('Request'),
      '#data' => $data,
    ];
  }

  /**
   * Render the content of a POST request.
   *
   * @param string $content
   *   The content of a POST request.
   * @param string $label
   *   The section label.
   *
   * @return array
   *   The render array of the content.
   */
  private function renderContent($content, $label): array {
    return [
      $label => [
        '#type' => 'inline_template',
        '#template' => '<h3>{{ title }}</h3> {{ data|raw }}',
        '#context' => [
          'title' => $this->t($label),
          'data' => $content,
        ],
      ],
    ];
  }

  /**
   * Render the list of access checks.
   *
   * @param array $accessChecks
   *   The list of access checks.
   * @param string $label
   *   The section label.
   *
   * @return array
   *   The render array of the list of access checks.
   */
  private function renderAccessChecks(array $accessChecks, $label): array {
    if (count($accessChecks) == 0) {
      return [];
    }

    $rows = [];
    /** @var \Symfony\Component\VarDumper\Cloner\Data $el */
    foreach ($accessChecks as $el) {
      $service_id = $el->getValue()[RequestDataCollector::SERVICE_ID];
      $callable = $el->getValue()[RequestDataCollector::CALLABLE];

      $rows[] = [
        [
          'data' => $service_id->getValue(),
          'class' => 'webprofiler__key',
        ],
        [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{{ data|raw }}',
            '#context' => [
              'data' => $this->dumpData($callable),
            ],
          ],
          'class' => 'webprofiler__value',
        ],
      ];
    }

    return [
      $label => [
        '#theme' => 'webprofiler_dashboard_table',
        '#title' => $this->t($label),
        '#data' => [
          '#type' => 'table',
          '#header' => [$this->t('Name'), $this->t('Value')],
          '#rows' => $rows,
          '#attributes' => [
            'class' => [
              'webprofiler__table',
            ],
          ],
        ],
      ],
    ];
  }

}
