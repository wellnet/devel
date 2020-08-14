<?php

namespace Drupal\webprofiler\EventListener;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\EventListener\ProfilerListener as SymfonyProfilerListener;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Class ProfilerListener.
 */
class ProfilerListener extends SymfonyProfilerListener {

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * ProfilerListener constructor.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profiler $profiler
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   * @param \Symfony\Component\HttpFoundation\RequestMatcherInterface $matcher
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   */
  public function __construct(Profiler $profiler, RequestStack $requestStack, RequestMatcherInterface $matcher, ConfigFactoryInterface $config) {
    $this->config = $config->get('webprofiler.settings');

    parent::__construct($profiler, $requestStack, $matcher, $this->config->get('only_exceptions'), $this->config->get('only_master_requests'));
  }

}
