<?php

namespace Drupal\webprofiler\Entity;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Entity\EntityLastInstalledSchemaRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\PhpStorage\PhpStorageFactory;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Class EntityTypeManagerWrapper.
 */
class EntityTypeManagerWrapper extends EntityTypeManager implements EntityTypeManagerInterface, ContainerAwareInterface {

  /**
   * @var array
   */
  private $loaded;

  /**
   * @var array
   */
  private $rendered;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityManager;

  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The original entity manager service.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to use.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class resolver.
   * @param \Drupal\Core\Entity\EntityLastInstalledSchemaRepositoryInterface $entity_last_installed_schema_repository
   *   The entity last installed schema repository.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, \Traversable $namespaces, ModuleHandlerInterface $module_handler, CacheBackendInterface $cache, TranslationInterface $string_translation, ClassResolverInterface $class_resolver, EntityLastInstalledSchemaRepositoryInterface $entity_last_installed_schema_repository) {
    $this->entityManager = $entity_manager;

    parent::__construct($namespaces, $module_handler, $cache, $string_translation, $class_resolver, $entity_last_installed_schema_repository);
  }

  /**
   * {@inheritdoc}
   */
  public function getStorage($entity_type) {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $handler */
    $handler = $this->getHandler($entity_type, 'storage');
    $type = ($handler instanceof ConfigEntityStorageInterface) ? 'config' : 'content';

    if (!isset($this->loaded[$type][$entity_type])) {
      $handler = $this->getStorageDecorator($entity_type, $handler);
      $this->loaded[$type][$entity_type] = $handler;
    }
    else {
      $handler = $this->loaded[$type][$entity_type];
    }

    return $handler;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewBuilder($entity_type) {
    /** @var \Drupal\Core\Entity\EntityViewBuilderInterface $handler */
    $handler = $this->getHandler($entity_type, 'view_builder');

    if ($handler instanceof EntityViewBuilderInterface) {
      if (!isset($this->rendered[$entity_type])) {
        $handler = new EntityViewBuilderDecorator($handler);
        $this->rendered[$entity_type] = $handler;
      }
      else {
        $handler = $this->rendered[$entity_type];
      }
    }

    return $handler;
  }

  /**
   * @param $type
   * @param $entity_type
   *
   * @return array
   */
  public function getLoaded($type, $entity_type) {
    return isset($this->loaded[$type][$entity_type]) ? $this->loaded[$type][$entity_type] : NULL;
  }

  /**
   * @param $entity_type
   *
   * @return array
   */
  public function getRendered($entity_type) {
    return isset($this->rendered[$entity_type]) ? $this->rendered[$entity_type] : NULL;
  }

  /**
   * Return a decorator for the storage handler.
   *
   * @param $entity_type
   * @param $handler
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  private function getStorageDecorator($entity_type, $handler) {
    // Loaded this way to avoid circular references.
    /** @var \Drupal\webprofiler\DecoratorGeneratorInterface $decoratorGenerator */
    $decoratorGenerator = \Drupal::service('webprofiler.config_entity_storage_decorator_generator');
    $decorators = $decoratorGenerator->getDecorators();

    $storage = PhpStorageFactory::get('webprofiler');
    if ($handler instanceof ConfigEntityStorageInterface) {
      if (array_key_exists($entity_type, $decorators)) {
        $storage->load($entity_type);
        if (!class_exists($decorators[$entity_type])) {
          try {
            $decoratorGenerator->generate();
            $storage->load($entity_type);
          }
          catch (\Exception $e) {
            return $handler;
          }
        }

        return new $decorators[$entity_type]($handler);
      }

      return new ConfigEntityStorageDecorator($handler);
    }

    return $handler;
  }

}
