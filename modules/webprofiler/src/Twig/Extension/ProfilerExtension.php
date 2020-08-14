<?php

namespace Drupal\webprofiler\Twig\Extension;


use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Twig\Environment;
use Twig\Extension\ProfilerExtension as ProfilerExtensionAlias;
use Twig\Profiler\Profile;
use Twig\TwigFunction;

/**
 * Class ProfilerExtension
 */
class ProfilerExtension extends ProfilerExtensionAlias {

  /**
   * @var HtmlDumper
   */
  private $dumper;

  /**
   * @var resource
   */
  private $output;

  public function __construct(Profile $profile, HtmlDumper $dumper = NULL) {
    parent::__construct($profile);

    $this->dumper = $dumper ?: new HtmlDumper();
    $this->dumper->setOutput($this->output = fopen('php://memory', 'r+b'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('profiler_dump', [
        $this,
        'dumpData',
      ], ['is_safe' => ['html'], 'needs_environment' => TRUE]),
    ];
  }

  /**
   * @param \Twig\Environment $env
   * @param \Symfony\Component\VarDumper\Cloner\Data $data
   * @param int $maxDepth
   *
   * @return mixed
   */
  public function dumpData(Environment $env, Data $data, $maxDepth = 0) {
    $this->dumper->setCharset($env->getCharset());
    $this->dumper->dump($data, NULL, [
      'maxDepth' => $maxDepth,
    ]);

    $dump = stream_get_contents($this->output, -1, 0);
    rewind($this->output);
    ftruncate($this->output, 0);

    return str_replace("\n</pre", '</pre', rtrim($dump));
  }

}
