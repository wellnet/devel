<?php

namespace Drupal\webprofiler\Twig\Extension;

use Symfony\Component\HttpKernel\Debug\FileLinkFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig extension relate to PHP code and used by Webprofiler.
 */
class CodeExtension extends AbstractExtension {

  /**
   * The File link formatter service.
   *
   * @var \Symfony\Component\HttpKernel\Debug\FileLinkFormatter
   */
  private FileLinkFormatter $fileLinkFormat;

  /**
   * CodeExtension constructor.
   *
   * @param \Symfony\Component\HttpKernel\Debug\FileLinkFormatter $file_link_format
   *   The File link formatter service.
   */
  public function __construct(FileLinkFormatter $file_link_format) {
    $this->fileLinkFormat = $file_link_format;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters(): array {
    return [
      new TwigFilter('abbr_class', [$this, 'abbrClass'],
        ['is_safe' => ['html']]),
      new TwigFilter('file_link', [$this, 'getFileLink']),
    ];
  }

  /**
   * Return the abbreviated form of a class name.
   *
   * @param string $class
   *   The class name to abbreviate.
   *
   * @return string
   *   The abbreviated form of a class name.
   */
  public function abbrClass($class) {
    $parts = explode('\\', $class);
    $short = array_pop($parts);

    return sprintf('<abbr title="%s">%s</abbr>', $class, $short);
  }

  /**
   * Returns the link for a given file/line pair.
   *
   * @param string $file
   *   An absolute file path.
   * @param int $line
   *   The line number.
   *
   * @return string|false
   *   A link or false.
   */
  public function getFileLink(string $file, int $line) {
    if ($fmt = $this->fileLinkFormat) {
      return \is_string($fmt) ? strtr($fmt,
        ['%f' => $file, '%l' => $line]) : $fmt->format($file, $line);
    }

    return FALSE;
  }

}
