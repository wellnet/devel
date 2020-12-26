<?php

namespace Drupal\webprofiler\Profiler;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Twig\Environment;

/**
 * Profiler Templates Manager.
 */
class TemplateManager {

  /**
   * The profiler service.
   *
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  protected $profiler;

  /**
   * The Twig environment service.
   *
   * @var \Twig\Environment
   */
  protected $twig;

  /**
   * Data collector templates retrieved by ProfilerPass class.
   *
   * @var array
   */
  protected $templates;

  /**
   * TemplateManager constructor.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profiler $profiler
   *   The profiler service.
   * @param \Twig\Environment $twig
   *   The Twig environment service.
   * @param array $templates
   *   Data collector templates retrieved by ProfilerPass class.
   */
  public function __construct(Profiler $profiler, Environment $twig, array $templates) {
    $this->profiler = $profiler;
    $this->twig = $twig;
    $this->templates = $templates;
  }

  /**
   * Get the template name for a given panel.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profile $profile
   *   A profile.
   * @param string $panel
   *   A data collector name.
   *
   * @return string
   *   The template name for a given panel.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function getName(Profile $profile, $panel) {
    $templates = $this->getNames($profile);

    if (!isset($templates[$panel])) {
      throw new NotFoundHttpException(sprintf('Panel "%s" is not registered in profiler or is not present in viewed profile.', $panel));
    }

    return $templates[$panel];
  }

  /**
   * Get template names of templates that are present in the viewed profile.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profile $profile
   *   A profile.
   *
   * @return array
   *   Template names of templates that are present in the viewed profile.
   */
  public function getNames(Profile $profile) {
    $templates = [];

    foreach ($this->templates as $arguments) {
      if (NULL === $arguments) {
        continue;
      }

      [$name, $template] = $arguments;

      if (!$this->profiler->has($name) || !$profile->hasCollector($name)) {
        continue;
      }

      if ('.html.twig' === substr($template, -10)) {
        $template = substr($template, 0, -10);
      }

      if (!$this->twig->getLoader()->exists($template . '.html.twig')) {
        throw new \UnexpectedValueException(sprintf('The profiler template "%s.html.twig" for data collector "%s" does not exist.', $template, $name));
      }

      $templates[$name] = $template . '.html.twig';
    }

    return $templates;
  }

}
