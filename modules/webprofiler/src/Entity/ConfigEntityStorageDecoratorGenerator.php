<?php

namespace Drupal\webprofiler\Entity;

use PhpParser\Node\Stmt\ClassMethod;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\PhpStorage\PhpStorageFactory;
use Drupal\webprofiler\DecoratorGeneratorInterface;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use Psr\Log\LoggerInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class DecoratorGenerator.
 */
class ConfigEntityStorageDecoratorGenerator implements DecoratorGeneratorInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  private $log;

  /**
   * DecoratorGenerator constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Psr\Log\LoggerInterface $log
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerInterface $log) {
    $this->entityTypeManager = $entity_type_manager;
    $this->log = $log;
  }

  /**
   * {@inheritdoc}
   */
  public function generate() {
    $classes = $this->getClasses();

    foreach ($classes as $class) {
      try {
        $php = $this->createDecorator($class);
        $this->writeDecorator($class['id'], $php);
      }
      catch (\Exception $e) {
        throw new \Exception('Unable to generate decorator for class ' . $class['class'] . '. ' . $e->getMessage());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDecorators(): array {
    return [
      'taxonomy_vocabulary' => '\Drupal\webprofiler\Entity\VocabularyStorageDecorator',
      'user_role' => '\Drupal\webprofiler\Entity\RoleStorageDecorator',
      'shortcut_set' => '\Drupal\webprofiler\Entity\ShortcutSetStorageDecorator',
      'image_style' => '\Drupal\webprofiler\Entity\ImageStyleStorageDecorator',
    ];
  }

  /**
   * @return array
   */
  public function getClasses(): array {
    $definitions = $this->entityTypeManager->getDefinitions();
    $classes = [];

    foreach ($definitions as $definition) {
      try {
        $classPath = $this->getClassPath($definition->getStorageClass());
        $ast = $this->getAST($classPath);

        $visitor = new FindingVisitor(function (Node $node) {
          return $this->isConfigEntityStorage($node);
        });

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->addVisitor(new NameResolver());
        $traverser->traverse($ast);

        $nodes = $visitor->getFoundNodes();

        /** @var \PhpParser\Node\Stmt\Class_ $node */
        foreach ($nodes as $node) {
          $classes[$definition->id()] = [
            'id' => $definition->id(),
            'class' => $node->name->name,
            'interface' => '\\' . implode('\\', $node->implements[0]->parts),
            'decoratorClass' => '\\Drupal\\webprofiler\\Entity\\' . $node->name->name . 'Decorator',
          ];
        }
      }
      catch (Error $error) {
        echo "Parse error: {$error->getMessage()}\n";
        return [];
      }
      catch (\ReflectionException $error) {
        echo "Reflection error: {$error->getMessage()}\n";
        return [];
      }
    }

    return $classes;
  }

  /**
   * @param string $class
   *
   * @return string
   *
   * @throws \ReflectionException
   */
  private function getClassPath(string $class): string {
    $reflector = new \ReflectionClass($class);
    $classPath = $reflector->getFileName();

    return $classPath;
  }

  /**
   * @param string $classPath
   *
   * @return \PhpParser\Node\Stmt[]|null
   */
  private function getAST(string $classPath): array {
    $code = file_get_contents($classPath);
    $parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);

    return $parser->parse($code);
  }

  /**
   * @param \PhpParser\Node $node
   *
   * @return bool
   */
  private function isConfigEntityStorage(Node $node): bool {
    if ($node instanceof Class_
      && $node->extends !== NULL &&
      $node->implements !== NULL &&
      $node->extends->parts[0] == 'ConfigEntityStorage' &&
      $node->implements[0]->parts[0] != ''
    ) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @param array $class
   *
   * @return string
   *
   * @throws \Exception
   */
  private function createDecorator(array $class): string {
    $decorator = $class['class'] . 'Decorator';

    $classPath = $this->getClassPath($class['interface']);
    $ast = $this->getAST($classPath);

    $nodeFinder = new NodeFinder();
    $nodes = $nodeFinder->find($ast, function (Node $node) {
      return $node instanceof ClassMethod;
    });

    $methods = [];
    /** @var \PhpParser\Node\Stmt\ClassMethod $node */
    foreach ($nodes as $node) {
      $params = [];
      /** @var \PhpParser\Node\Param $param */
      foreach ($node->getParams() as $param) {
        $params[] = [
          'name' => $param->var->name,
        ];
      }

      $methods[] = [
        'name' => $node->name->name,
        'params' => $params,
      ];
    }

    try {
      /** @var \Twig\Environment $twig */
      $twig = \Drupal::service('twig');
      $php = $twig->render('@webprofiler/Decorator/storageDecorator.php.twig', [
        'decorator' => $decorator,
        'interface' => $class['interface'],
        'methods' => $methods,
      ]);

      return $php;
    }
    catch (LoaderError $e) {
      throw new \Exception('Unable to create a decorator. ' . $e->getMessage());
    }
    catch (RuntimeError $e) {
      throw new \Exception('Unable to create a decorator. ' . $e->getMessage());
    }
    catch (SyntaxError $e) {
      throw new \Exception('Unable to create a decorator. ' . $e->getMessage());
    }
  }

  /**
   * @param string $name
   * @param string $php
   */
  private function writeDecorator(string $name, string $php) {
    $storage = PhpStorageFactory::get('webprofiler');

    if (!$storage->exists($name)) {
      $storage->save($name, $php);
    }
  }

}
