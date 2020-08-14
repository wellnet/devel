<?php

namespace Drupal\webprofiler\Compiler;

use Drupal\Core\StreamWrapper\PublicStream;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ProfilerPass.
 */
class ProfilerPass implements CompilerPassInterface {

  /**
   * {@inheritDoc}
   */
  public function process(ContainerBuilder $container) {
    if (false === $container->hasDefinition('webprofiler.profiler')) {
      return;
    }

    $definition = $container->getDefinition('webprofiler.profiler');

    $collectors = new \SplPriorityQueue();
    $order = PHP_INT_MAX;
    foreach ($container->findTaggedServiceIds('data_collector', true) as $id => $attributes) {
      $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
      $template = null;

      if (isset($attributes[0]['template'])) {
        if (!isset($attributes[0]['id'])) {
          throw new InvalidArgumentException(sprintf('Data collector service "%s" must have an id attribute in order to specify a template', $id));
        }
        $template = [$attributes[0]['id'], $attributes[0]['template']];
      }

      $collectors->insert([$id, $template], [$priority, --$order]);
    }

    $templates = [];
    foreach ($collectors as $collector) {
      $definition->addMethodCall('add', [new Reference($collector[0])]);
      $templates[$collector[0]] = $collector[1];
    }

    $container->setParameter('data_collector.templates', $templates);

    // Set a parameter with the storage dns.
    $path = 'file:' . DRUPAL_ROOT . '/' . PublicStream::basePath() . '/profiler';
    $container->setParameter('webprofiler.file_profiler_storage_dns', $path);
  }
}
