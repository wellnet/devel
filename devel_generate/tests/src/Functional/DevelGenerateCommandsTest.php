<?php
namespace Drupal\Tests\devel_generate\Functional;

use Drupal\comment\Entity\Comment;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\Entity\Node;
use Drupal\system\Entity\Menu;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\devel_generate\Traits\DevelGenerateSetupTrait;
use Drupal\user\Entity\User;
use Drush\TestTraits\DrushTestTrait;

/**
 * Note: Drush must be in the Composer project. See https://cgit.drupalcode.org/devel/tree/drupalci.yml?h=8.x-2.x and its docs at
 * https://www.drupal.org/drupalorg/docs/drupal-ci/customizing-drupalci-testing-for-projects
 */

/**
 * @coversDefaultClass \Drupal\devel_generate\Commands\DevelGenerateCommands
 * @group devel-generate
 */
class DevelGenerateCommandsTest extends BrowserTestBase
{
  use DrushTestTrait;
  use DevelGenerateSetupTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['menu_ui', 'node', 'comment', 'taxonomy', 'path', 'devel_generate'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Prepares the testing environment
   */
  public function setUp() {
    parent::setUp();
    $this->setUpData();
  }

  public function testGeneration() {
    // Make sure users get created, and with correct roles.
    $this->drush('devel-generate-users', [55], ['kill' => null, 'roles' => 'administrator']);
    $user = User::load(55);
    $this->assertTrue($user->hasRole('administrator'));

    // Make sure terms get created, and with correct vocab.
    $this->drush('devel-generate-terms', [55], ['kill' => null, 'bundles' => $this->vocabulary->id()]);
    $term = Term::load(55);
    $this->assertEquals($this->vocabulary->id(), $term->bundle());

    // Make sure vocabs get created.
    $this->drush('devel-generate-vocabs', [5], ['kill' => null]);
    $vocabs = Vocabulary::loadMultiple();
    $this->assertGreaterThan(4, count($vocabs));
    $vocab = array_pop($vocabs);
    $this->assertNotEmpty($vocab);

    // Make sure menus, and with correct properties.
    $this->drush('devel-generate-menus', [1, 5], ['kill' => null]);
    $menus = Menu::loadMultiple();
    foreach ($menus as $key => $menu) {
      if (strstr($menu->id(), 'devel-') !== FALSE) {
        // We have a menu that we created.
        break;
      }
    }
    $link = MenuLinkContent::load(5);
    $this->assertEquals($menu->id(), $link->getMenuName());

    // Make sure content gets created.
    $this->drush('devel-generate-content', [21], ['kill' => NULL]);
    $node = Node::load(21);
    $this->assertNotEmpty($node);

    // Make sure articles get comments. Only one third of articles will have
    // comment status 'open' and therefore the ability to receive a comment.
    // However generating 30 articles will give the likelyhood of test failure
    // (i.e. no article gets a comment) as 2/3 ^ 30 = 0.00052% or 1 in 191751.
    $this->drush('devel-generate-content', [30, 9], ['kill' => NULL, 'bundles' => 'article']);
    $comment = Comment::load(1);
    $this->assertNotEmpty($comment);

    // Generate content with a higher number that triggers batch running.
    $this->drush('devel-generate-content', [55], ['kill' => null]);
    $node = Node::load(55);
    $this->assertNotEmpty($node);
    $messages = $this->getErrorOutput();
    $this->assertContains('Finished 55 elements created successfully.', $messages, 'devel-generate-content batch ending message not found', TRUE);
  }
}
