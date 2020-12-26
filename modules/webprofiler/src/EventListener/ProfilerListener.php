<?php

namespace Drupal\webprofiler\EventListener;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\EventListener\ProfilerListener as SymfonyProfilerListener;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * ProfilerListener collects data for the current request.
 */
class ProfilerListener extends SymfonyProfilerListener {

  /**
   * An immutable config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * ProfilerListener constructor.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profiler $profiler
   *   The profiler service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   * @param \Symfony\Component\HttpFoundation\RequestMatcherInterface $matcher
   *   The request matcher service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory service.
   */
  public function __construct(Profiler $profiler, RequestStack $requestStack, RequestMatcherInterface $matcher, ConfigFactoryInterface $config) {
    $this->config = $config->get('webprofiler.settings');

    parent::__construct($profiler, $requestStack, $matcher, FALSE, FALSE);
  }

}
