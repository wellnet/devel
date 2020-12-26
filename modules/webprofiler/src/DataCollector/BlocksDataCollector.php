<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\webprofiler\Entity\EntityDecorator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * DataCollector for Drupal blocks.
 */
class BlocksDataCollector extends DataCollector {

  /**
   * The Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityManager;

  /**
   * BlocksDataCollector constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   The Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityManager) {
    $this->entityManager = $entityManager;

    $this->data['blocks']['loaded'] = [];
    $this->data['blocks']['rendered'] = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'blocks';
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $storage = $this->entityManager->getStorage('block');

    $loaded = $this->entityManager->getLoaded('config', 'block');
    $rendered = $this->entityManager->getRendered('block');

    if ($loaded) {
      $this->data['blocks']['loaded'] = $this->getBlocksData($loaded, $storage);
    }

    if ($rendered) {
      $this->data['blocks']['rendered'] = $this->getBlocksData($rendered, $storage);
    }
  }

  /**
   * Return a list of rendered blocks.
   *
   * @return array
   *   A list of rendered blocks.
   */
  public function getRenderedBlocks() {
    return $this->data['blocks']['rendered'];
  }

  /**
   * Return the number of rendered blocks.
   *
   * @return int
   *   The number of rendered blocks.
   */
  public function getRenderedBlocksCount() {
    return count($this->getRenderedBlocks());
  }

  /**
   * Return a list of loaded blocks.
   *
   * @return array
   *   A list of loaded blocks.
   */
  public function getLoadedBlocks() {
    return $this->data['blocks']['loaded'];
  }

  /**
   * Return the number of loaded blocks.
   *
   * @return int
   *   The number of rendered blocks.
   */
  public function getLoadedBlocksCount() {
    return count($this->getLoadedBlocks());
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {

  }

  /**
   * Return the data to store about blocks.
   *
   * @param \Drupal\webprofiler\Entity\EntityDecorator $decorator
   *   An entity decorator.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The block storage service.
   *
   * @return array
   *   The data to store about blocks.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  private function getBlocksData(EntityDecorator $decorator, EntityStorageInterface $storage) {
    $blocks = [];

    /** @var \Drupal\block\BlockInterface $block */
    foreach ($decorator->getEntities() as $block) {
      /** @var \Drupal\block\Entity\Block $entity */
      if (NULL !== $block && $entity = $storage->load($block->get('id'))) {

        $route = '';
        if ($entity->hasLinkTemplate('edit-form')) {
          $route = $entity->toUrl('edit-form')->toString();
        }

        $id = $block->get('id');
        $blocks[$id] = [
          'id' => $id,
          'region' => $block->getRegion(),
          'status' => $block->get('status'),
          'theme' => $block->getTheme(),
          'plugin' => $block->get('plugin'),
          'settings' => $block->get('settings'),
          'route' => $route,
        ];
      }
    }

    return $blocks;
  }

}
