<?php

namespace Drupal\webprofiler\RequestMatcher;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * Exclude some path to be profiled.
 */
class WebprofilerRequestMatcher implements RequestMatcherInterface {

  /**
   * An immutable config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  /**
   * The path matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  private $pathMatcher;

  /**
   * WebprofilerRequestMatcher constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory service.
   * @param \Drupal\Core\Path\PathMatcherInterface $pathMatcher
   *   The path matcher service.
   */
  public function __construct(ConfigFactoryInterface $config, PathMatcherInterface $pathMatcher) {
    $this->config = $config->get('webprofiler.settings');

    $this->pathMatcher = $pathMatcher;
  }

  /**
   * {@inheritdoc}
   */
  public function matches(Request $request) {
    $path = $request->getPathInfo();

    $patterns = $this->config->get('exclude_paths');

    // Never add Webprofiler to phpinfo page.
    $patterns .= "\r\n/admin/reports/status/php";

    // Never add Webprofiler to uninstall confirm page.
    $patterns .= "\r\n/admin/modules/uninstall/*";

    return !$this->pathMatcher->matchPath($path, $patterns);
  }

}
