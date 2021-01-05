<?php

namespace Drupal\webprofiler;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Debug\FileLinkFormatter;

/**
 * Factory class to create FileLinkFormatter service instances.
 */
class FileLinkFormatterFactory {

  /**
   * Return a FileLinkFormatter configured with webprofiler settings.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   *
   * @return \Symfony\Component\HttpKernel\Debug\FileLinkFormatter
   *   A FileLinkFormatter configured with webprofiler settings.
   */
  final public static function getFileLinkFormatter(
    RequestStack $requestStack,
    ConfigFactoryInterface $configFactory
  ): FileLinkFormatter {
    $settings = $configFactory->get('webprofiler.settings');
    $ide = $settings->get('ide');
    $ide_remote_path = $settings->get('ide_remote_path');
    $ide_local_path = $settings->get('ide_local_path');

    $link_format = sprintf('%s&%s>%s', $ide, $ide_remote_path, $ide_local_path);

    return new FileLinkFormatter($link_format, $requestStack);
  }

}
