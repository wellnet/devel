<?php

namespace Drupal\webprofiler\Panel;

/**
 * Panel to render collected data about user.
 *
 * @package Drupal\webprofiler\Panel
 */
class UserPanel extends PanelBase implements PanelInterface {

  /**
   * {@inheritDoc}
   */
  public function render($token, $name): array {
    /** @var \Symfony\Component\HttpKernel\Profiler\Profiler $profiler */
    $profiler = \Drupal::service('webprofiler.profiler');
    /** @var \Drupal\webprofiler\DataCollector\UserDataCollector $collector */
    $collector = $profiler->loadProfile($token)->getCollector($name);

    // TODO: implement a better UX!
    $render = [
      'username' => [
        '#type' => 'item',
        '#title' => t('Username'),
        '#plain_text' => $collector->getUserName(),
      ],
      'roles' => [
        '#type' => 'item',
        '#title' => t('Roles'),
        '#plain_text' => implode(', ', $collector->getRoles()),
      ],
      'provider' => [
        '#type' => 'item',
        '#title' => t('Provider'),
        '#plain_text' => $collector->getProvider(),
      ],
    ];

    return [
      '#theme' => 'webprofiler_dashboard_panel',
      '#title' => $this->t('User'),
      '#data' => $render,
    ];
  }

}
