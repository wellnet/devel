<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\webprofiler\Panel\DrupalPanel;
use Drupal\webprofiler\Panel\PanelInterface;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Drupal;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

/**
 * DataCollector for Drupal.
 *
 * @package Drupal\webprofiler\DataCollector
 */
class DrupalDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  /**
   * Redirect destination.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  /**
   * DrupalDataCollector constructor.
   *
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirectDestination
   *   The redirect destination service.
   */
  public function __construct(RedirectDestinationInterface $redirectDestination) {
    $this->redirectDestination = $redirectDestination;
  }

  /**
   * {@inheritDoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['version'] = Drupal::VERSION;
    $this->data['profile'] = \Drupal::installProfile();
    $this->data['config_url'] = (new Url('webprofiler.settings', [], ['query' => $this->redirectDestination->getAsArray()]))->toString();

    try {
      $process = new Process("git log -1 --pretty=format:'%H - %s (%ci)' --abbrev-commit");
      $process->setTimeout(3600);
      $process->mustRun();
      $this->data['git_commit'] = $process->getOutput();

      $process = new Process("git log -1 --pretty=format:'%h' --abbrev-commit");
      $process->setTimeout(3600);
      $process->mustRun();
      $this->data['abbr_git_commit'] = $process->getOutput();
    }
    catch (ProcessFailedException $e) {
      $this->data['git_commit'] = $this->data['git_commit_abbr'] = NULL;
    }
    catch (RuntimeException $e) {
      $this->data['git_commit'] = $this->data['git_commit_abbr'] = NULL;
    }
  }

  /**
   * Get Drupal core version.
   *
   * @return string
   *   The drupal core version.
   */
  public function getVersion() {
    return $this->data['version'];
  }

  /**
   * Get current profile.
   *
   * @return string
   *   The name of profile.
   */
  public function getProfile() {
    return $this->data['profile'];
  }

  /**
   * Get the config url.
   *
   * @return string
   *   An url.
   */
  public function getConfigUrl() {
    return $this->data['config_url'];
  }

  /**
   * Get GIT commit.
   *
   * @return string
   *   The git commit.
   */
  public function getGitCommit() {
    return $this->data['git_commit'];
  }

  /**
   * Get abbrGit commit.
   *
   * @return string
   *   The git commit id.
   */
  public function getAbbrGitCommit() {
    return $this->data['abbr_git_commit'];
  }

  /**
   * {@inheritDoc}
   */
  public function getName() {
    return 'drupal';
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    $this->data = [];
  }

  /**
   * {@inheritDoc}
   */
  public function getPanel(): PanelInterface {
    return new DrupalPanel();
  }

}
