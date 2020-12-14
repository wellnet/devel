<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\webprofiler\Entity\EntityDecorator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 *
 */
class BlocksDataCollector extends DataCollector {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityManager;

  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
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
   * @return array
   */
  public function getRenderedBlocks() {
    return $this->data['blocks']['rendered'];
  }

  /**
   * @return int
   */
  public function getRenderedBlocksCount() {
    return count($this->getRenderedBlocks());
  }

  /**
   * @return array
   */
  public function getLoadedBlocks() {
    return $this->data['blocks']['loaded'];
  }

  /**
   * @return int
   */
  public function getLoadedBlocksCount() {
    return count($this->getLoadedBlocks());
  }

  /**
   *
   */
  public function reset() {

  }

  /**
   * @param \Drupal\webprofiler\Entity\EntityDecorator $decorator
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *
   * @return array
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
