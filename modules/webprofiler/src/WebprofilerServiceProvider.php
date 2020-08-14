<?php

namespace Drupal\webprofiler;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\webprofiler\Compiler\ProfilerPass;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Defines a service profiler for the webprofiler module.
 */
class WebprofilerServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    // Add a compiler pass to discover all data collector services.
    $container->addCompilerPass(new ProfilerPass());

    $modules = $container->getParameter('container.modules');

    // Add BlockDataCollector only if Block module is enabled.
    if (isset($modules['block'])) {
      $container->register('webprofiler.blocks', 'Drupal\webprofiler\DataCollector\BlocksDataCollector')
        ->addArgument(new Reference(('entity_type.manager')))
        ->addTag('data_collector', [
          'template' => '@webprofiler/Collector/blocks.html.twig',
          'id' => 'blocks',
          'title' => 'Blocks',
          'priority' => 78,
        ]);
    }
  }

}
