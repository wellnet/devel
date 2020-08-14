<?php

namespace Drupal\webprofiler\RequestMatcher;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * Class WebprofilerRequestMatcher
 */
class WebprofilerRequestMatcher implements RequestMatcherInterface {

  /**
   * @var ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * @var PathMatcherInterface
   */
  private $pathMatcher;

  /**
   * @param ConfigFactoryInterface $configFactory
   * @param PathMatcherInterface $pathMatcher
   */
  public function __construct(ConfigFactoryInterface $configFactory, PathMatcherInterface $pathMatcher) {
    $this->configFactory = $configFactory;
    $this->pathMatcher = $pathMatcher;
  }

  /**
   * {@inheritdoc}
   */
  public function matches(Request $request) {
    $path = $request->getPathInfo();

    $patterns = $this->configFactory->get('webprofiler.settings')->get('exclude_paths');

    // never add Webprofiler to phpinfo page.
    $patterns .= "\r\n/admin/reports/status/php";

    // never add Webprofiler to uninstall confirm page.
    $patterns .= "\r\n/admin/modules/uninstall/*";

    return !$this->pathMatcher->matchPath($path, $patterns);
  }
}
