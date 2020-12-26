<?php

namespace Drupal\webprofiler\Entity;

use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Decorator for entity view builder handlers.
 */
class EntityViewBuilderDecorator extends EntityDecorator implements EntityHandlerInterface, EntityViewBuilderInterface {

  /**
   * EntityViewBuilderDecorator constructor.
   *
   * @param \Drupal\Core\Entity\EntityViewBuilderInterface $config_entity_storage
   *   The config entity storage to decorate.
   */
  final public function __construct(EntityViewBuilderInterface $config_entity_storage) {
    parent::__construct($config_entity_storage);

    $this->entities = [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode, $langcode = NULL) {
    $this->getOriginalObject()
      ->buildComponents($build, $entities, $displays, $view_mode, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $this->entities[] = $entity;

    return $this->getOriginalObject()->view($entity, $view_mode, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function viewMultiple(array $entities = [], $view_mode = 'full', $langcode = NULL) {
    $this->entities = array_merge($this->entities, $entities);

    return $this->getOriginalObject()
      ->viewMultiple($entities, $view_mode, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache(array $entities = NULL) {
    $this->getOriginalObject()->resetCache($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function viewField(FieldItemListInterface $items, $display_options = []) {
    return $this->getOriginalObject()->viewField($items, $display_options);
  }

  /**
   * {@inheritdoc}
   */
  public function viewFieldItem(FieldItemInterface $item, $display_options = []) {
    return $this->getOriginalObject()->viewFieldItem($item, $display_options);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return $this->getOriginalObject()->getCacheTag();
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager'),
      $container->get('language_manager')
    );
  }

}
