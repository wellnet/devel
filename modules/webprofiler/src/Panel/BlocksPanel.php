<?php

namespace Drupal\webprofiler\Panel;

/**
 * Panel to render collected data about blocks.
 */
class BlocksPanel extends PanelBase implements PanelInterface {

  /**
   * {@inheritDoc}
   */
  public function render($token, $name): array {
    /** @var \Symfony\Component\HttpKernel\Profiler\Profiler $profiler */
    $profiler = \Drupal::service('webprofiler.profiler');
    /** @var \Drupal\webprofiler\DataCollector\BlocksDataCollector $collector */
    $collector = $profiler->loadProfile($token)->getCollector($name);

    $data = array_merge(
      $this->renderBlocks($collector->getLoadedBlocks(), 'Loaded'),
      $this->renderBlocks($collector->getRenderedBlocks(), 'Rendered'),
    );

    return [
      '#theme' => 'webprofiler_dashboard_panel',
      '#title' => $this->t('Blocks'),
      '#data' => $data,
    ];
  }

  /**
   * Render a list of blocks.
   *
   * @param array $blocks
   *   The list of blocks to render.
   * @param string $label
   *   The list label.
   *
   * @return array
   *   The render array of the list of blocks.
   */
  protected function renderBlocks(array $blocks, string $label): array {
    if (count($blocks) == 0) {
      return [
        $label => [
          '#markup' => '<p>' . $this->t('No @label blocks collected',
            ['@label' => $label]) . '</p>',
        ],
      ];
    }

    $rows = [];
    foreach ($blocks as $block) {
      $rows[] = [
        $block['id'],
        $block['settings']['label'],
        $block['region'] ?? 'No region',
        $block['settings']['provider'],
        $block['theme'],
        $block['status'] ? $this->t('Enabled') : $this->t('Disabled'),
        $block['plugin'],
      ];
    }

    return [
      $label => [
        '#theme' => 'webprofiler_dashboard_table',
        '#title' => $label,
        '#data' => [
          '#type' => 'table',
          '#header' => [
            $this->t('ID'),
            $this->t('Label'),
            $this->t('Region'),
            $this->t('Source'),
            [
              'data' => $this->t('Theme'),
              'class' => [RESPONSIVE_PRIORITY_LOW],
            ],
            $this->t('Status'),
            [
              'data' => $this->t('Plugin'),
              'class' => [RESPONSIVE_PRIORITY_LOW],
            ],
          ],
          '#rows' => $rows,
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
