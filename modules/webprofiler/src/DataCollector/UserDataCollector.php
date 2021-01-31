<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Authentication\AuthenticationCollectorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\webprofiler\Panel\PanelInterface;
use Drupal\webprofiler\Panel\UserPanel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * DataCollector for Drupal User.
 *
 * @package Drupal\webprofiler\DataCollector
 */
class UserDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Entity Type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Provider collector.
   *
   * @var \Drupal\Core\Authentication\AuthenticationCollectorInterface
   */
  protected $providerCollector;

  /**
   * UserDataCollector constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Authentication\AuthenticationCollectorInterface $provider_collector
   */
  public function __construct(AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory, AuthenticationCollectorInterface $provider_collector) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->providerCollector = $provider_collector;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['name'] = $this->currentUser->getDisplayName();
    $this->data['authenticated'] = $this->currentUser->isAuthenticated();

    $this->data['roles'] = [];
    $storage = $this->entityTypeManager->getStorage('user_role');
    foreach ($this->currentUser->getRoles() as $role) {
      $entity = $storage->load($role);
      if ($entity) {
        $this->data['roles']['label'] = $entity->label();
      }
    }

    foreach ($this->providerCollector->getSortedProviders() as $provider_id => $provider) {
      if ($provider->applies($request)) {
        $this->data['provider'] = $provider_id;
      }
    }

    $this->data['anonymous'] = $this->configFactory->get('user.settings')
      ->get('anonymous');
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'user';
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel(): PanelInterface {
    return new UserPanel();
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    $this->data = [];
  }

  public function getDisplayName() {
    return $this->data['name'];
  }

  /**
   * @return bool
   */
  public function getAuthenticated() {
    return $this->data['authenticated'];
  }

  /**
   * @return string
   */
  public function getAnonymous() {
    return $this->data['anonymous'];
  }

  /**
   * @return array
   */
  public function getRoles() {
    return $this->data['roles'];
  }

  /**
   * @return string
   */
  public function getProvider() {
    return $this->data['provider'];
  }

  /**
   * @return \Drupal\Core\Session\AccountInterface
   */
  public function getUserName() {
    return $this->data['name'];
  }

}
