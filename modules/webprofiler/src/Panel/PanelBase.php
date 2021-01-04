<?php

namespace Drupal\webprofiler\Panel;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

/**
 * Base class for dashboard panels.
 */
class PanelBase {

  use StringTranslationTrait;

  /**
   * A data dumper for HTML output.
   *
   * @var \Twig\Profiler\Dumper\HtmlDumper
   */
  private $dumper;

  /**
   * Internal resource to store dumped data.
   *
   * @var resource
   */
  private $output;

  /**
   * PanelBase constructor.
   *
   * @param \Symfony\Component\VarDumper\Dumper\HtmlDumper|null $dumper
   *   A data dumper for HTML output.
   */
  public function __construct(HtmlDumper $dumper = NULL) {
    $this->dumper = $dumper ?: new HtmlDumper();
    $this->dumper->setOutput($this->output = fopen('php://memory', 'r+b'));
    $this->dumper->setTheme('light');
  }

  /**
   * Dump data using a dumper.
   *
   * @param \Symfony\Component\VarDumper\Cloner\Data $data
   *   The data to dump.
   * @param int $maxDepth
   *   The max depth to dump for complex data.
   *
   * @return string|string[]
   *   The string representation of dumped data.
   */
  public function dumpData(Data $data, $maxDepth = 0) {
    $this->dumper->dump($data, NULL, [
      'maxDepth' => $maxDepth,
    ]);

    $dump = stream_get_contents($this->output, -1, 0);
    rewind($this->output);
    ftruncate($this->output, 0);

    return str_replace("\n</pre", '</pre', rtrim($dump));
  }

  /**
   * Render data in an array as HTML table.
   *
   * @param array $data
   *   The data to render.
   * @param string $label
   *   The table label.
   * @param callable|null $element_converter
   *   An optional function to convert all elements of data before rendering.
   *   If NULL fallback to PanelBase::dumpData.
   *
   * @return array
   *   A render array.
   */
  protected function renderTable(
    array $data,
    $label,
    callable $element_converter = NULL
  ): array {
    if (count($data) == 0) {
      return [];
    }

    if ($element_converter == NULL) {
      $element_converter = [$this, 'dumpData'];
    }

    $rows = [];
    foreach ($data as $key => $el) {
      $rows[] = [
        [
          'data' => $key,
          'class' => 'webprofiler__key',
        ],
        [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{{ data|raw }}',
            '#context' => [
              'data' => $element_converter($el),
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
