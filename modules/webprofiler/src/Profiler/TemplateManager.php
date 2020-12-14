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

  protected $twig;

  protected $templates;

  protected $profiler;

  /**
   * TemplateManager constructor.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profiler $profiler
   * @param \Twig\Environment $twig
   * @param array $templates
   */
  public function __construct(Profiler $profiler, Environment $twig, array $templates) {
    $this->profiler = $profiler;
    $this->twig = $twig;
    $this->templates = $templates;
  }

  /**
   * Gets the template name for a given panel.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profile $profile
   * @param string $panel
   *
   * @return mixed
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
   * Gets template names of templates that are present in the viewed profile.
   *
   * @return array
   *
   * @throws \UnexpectedValueException
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
