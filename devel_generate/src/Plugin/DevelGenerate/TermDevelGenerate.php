<?php

namespace Drupal\devel_generate\Plugin\DevelGenerate;

use Drupal\content_translation\ContentTranslationManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\devel_generate\DevelGenerateBase;
use Drupal\taxonomy\TermInterface;
use Drush\Utils\StringUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a TermDevelGenerate plugin.
 *
 * @DevelGenerate(
 *   id = "term",
 *   label = @Translation("terms"),
 *   description = @Translation("Generate a given number of terms. Optionally delete current terms."),
 *   url = "term",
 *   permission = "administer devel_generate",
 *   settings = {
 *     "num" = 10,
 *     "title_length" = 12,
 *     "kill" = FALSE,
 *   }
 * )
 */
class TermDevelGenerate extends DevelGenerateBase implements ContainerFactoryPluginInterface {

  /**
   * The vocabulary storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $vocabularyStorage;

  /**
   * The term storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The content translation manager.
   *
   * @var \Drupal\content_translation\ContentTranslationManagerInterface
   */
  protected $contentTranslationManager;

  /**
   * Constructs a new TermDevelGenerate object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $vocabulary_storage
   *   The vocabulary storage.
   * @param \Drupal\Core\Entity\EntityStorageInterface $term_storage
   *   The term storage.
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\content_translation\ContentTranslationManagerInterface $content_translation_manager
   *   The content translation manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $vocabulary_storage, EntityStorageInterface $term_storage, Connection $database, ModuleHandlerInterface $module_handler, LanguageManagerInterface $language_manager, ContentTranslationManagerInterface $content_translation_manager = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->vocabularyStorage = $vocabulary_storage;
    $this->termStorage = $term_storage;
    $this->database = $database;
    $this->moduleHandler = $module_handler;
    $this->languageManager = $language_manager;
    $this->contentTranslationManager = $content_translation_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $entity_type_manager = $container->get('entity_type.manager');
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $entity_type_manager->getStorage('taxonomy_vocabulary'),
      $entity_type_manager->getStorage('taxonomy_term'),
      $container->get('database'),
      $container->get('module_handler'),
      $container->get('language_manager'),
      $container->has('content_translation.manager') ? $container->get('content_translation.manager') : NULL
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $options = [];
    foreach ($this->vocabularyStorage->loadMultiple() as $vocabulary) {
      $options[$vocabulary->id()] = $vocabulary->label();
    }
    $form['vids'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => $this->t('Vocabularies'),
      '#required' => TRUE,
      '#default_value' => 'tags',
      '#options' => $options,
      '#description' => $this->t('Restrict terms to these vocabularies.'),
    ];
    $form['num'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of terms?'),
      '#default_value' => $this->getSetting('num'),
      '#required' => TRUE,
      '#min' => 0,
    ];
    $form['title_length'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum number of characters in term names'),
      '#default_value' => $this->getSetting('title_length'),
      '#required' => TRUE,
      '#min' => 2,
      '#max' => 255,
    ];
    $form['kill'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Delete existing terms in specified vocabularies before generating new terms.'),
      '#default_value' => $this->getSetting('kill'),
    ];

    // Add the language and translation options.
    $form += $this->getLanguageForm('terms');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function generateElements(array $values) {
    if ($values['kill']) {
      $this->deleteVocabularyTerms($values['vids']);
      $this->setMessage($this->t('Deleted existing terms.'));
    }

    $new_terms = $this->generateTerms($values);
    if (!empty($new_terms['terms'])) {
      $this->setMessage($this->t('Created the following new terms: @terms', ['@terms' => implode(', ', $new_terms['terms'])]));
    }
    if ($new_terms['terms_translations'] > 0) {
      $this->setMessage($this->formatPlural($new_terms['terms_translations'], 'Created 1 term translation', 'Created @count term translations'));
    }
  }

  /**
   * Deletes all terms of given vocabularies.
   *
   * @param array $vids
   *   Array of vocabulary vid.
   */
  protected function deleteVocabularyTerms(array $vids) {
    $tids = $this->vocabularyStorage->getToplevelTids($vids);
    $terms = $this->termStorage->loadMultiple($tids);
    $this->termStorage->delete($terms);
  }

  /**
   * Generates taxonomy terms for a list of given vocabularies.
   *
   * @param array $parameters
   *   The input parameters from the settings form.
   *
   * @return array
   *   Information about the created terms.
   */
  protected function generateTerms(array $parameters) {
    $info = [
      'terms' => [],
      'terms_translations' => 0,
    ];
    $vocabs = $this->vocabularyStorage->loadMultiple($parameters['vids']);

    // Insert new data:
    $max = $this->database->query('SELECT MAX(tid) FROM {taxonomy_term_data}')->fetchField();
    for ($i = 1; $i <= $parameters['num']; $i++) {
      $name = $this->getRandom()->word(mt_rand(2, $parameters['title_length']));

      $values = [
        'name' => $name,
        'description' => 'description of ' . $name,
        'format' => filter_fallback_format(),
        'weight' => mt_rand(0, 10),
      ];

      if (isset($parameters['add_language'])) {
        $values['langcode'] = $this->getLangcode($parameters['add_language']);
      }

      switch ($i % 2) {
        case 1:
          $vocab = $vocabs[array_rand($vocabs)];
          $values['vid'] = $vocab->id();
          $values['parent'] = [0];
          break;

        default:
          while (TRUE) {
            // Keep trying to find a random parent.
            $candidate = mt_rand(1, $max);
            $query = $this->database->select('taxonomy_term_data', 't');
            $parent = $query
              ->fields('t', ['tid', 'vid'])
              ->condition('t.vid', array_keys($vocabs), 'IN')
              ->condition('t.tid', $candidate, '>=')
              ->range(0, 1)
              ->execute()
              ->fetchAssoc();
            if ($parent['tid']) {
              break;
            }
          }
          $values['parent'] = [$parent['tid']];
          // Slight speedup due to this property being set.
          $values['vid'] = $parent['vid'];
          break;
      }

      $term = $this->termStorage->create($values);

      // A flag to let hook implementations know that this is a generated term.
      $term->devel_generate = TRUE;

      // Populate all fields with sample values.
      $this->populateFields($term);
      $term->save();

      // Add translations.
      if (isset($parameters['translate_language']) && !empty($parameters['translate_language'])) {
        $info['terms_translations'] += $this->generateTermTranslation($parameters['translate_language'], $term);
      }

      $max++;

      // Limit memory usage. Only report first 20 created terms.
      if ($i < 20) {
        $info['terms'][] = $term->label();
      }

      unset($term);
    }

    return $info;
  }

  /**
   * Create translation for the given term.
   *
   * @param array $translate_language
   *   Potential translate languages array.
   * @param \Drupal\taxonomy\TermInterface $term
   *   Term to add translations to.
   *
   * @return int
   *   Number of translations added.
   */
  protected function generateTermTranslation(array $translate_language, TermInterface $term) {
    if (is_null($this->contentTranslationManager)) {
      return 0;
    }
    if (!$this->contentTranslationManager->isEnabled('taxonomy_term', $term->bundle())) {
      return 0;
    }
    if ($term->langcode == LanguageInterface::LANGCODE_NOT_SPECIFIED || $term->langcode == LanguageInterface::LANGCODE_NOT_APPLICABLE) {
      return 0;
    }

    $num_translations = 0;
    // Translate term to each target language.
    $skip_languages = [
      LanguageInterface::LANGCODE_NOT_SPECIFIED,
      LanguageInterface::LANGCODE_NOT_APPLICABLE,
      $term->langcode->value,
    ];
    foreach ($translate_language as $langcode) {
      if (in_array($langcode, $skip_languages)) {
        continue;
      }
      $translation_term = $term->addTranslation($langcode);
      $translation_term->setName($term->getName() . ' (' . $langcode . ')');
      $this->populateFields($translation_term);
      $translation_term->save();
      $num_translations++;
    }
    return $num_translations;
  }

  /**
   * {@inheritdoc}
   */
  public function validateDrushParams(array $args, array $options = []) {
    if ($this->isDrush8()) {
      $bundles = _convert_csv_to_array(drush_get_option('bundles'));
    }
    else {
      $bundles = StringUtils::csvToarray($options['bundles']);
    }
    if (count($bundles) < 1) {
      throw new \Exception(dt('Please provide a vocabulary machine name (--bundles).'));
    }
    foreach ($bundles as $bundle) {
      // Verify that each bundle is a valid vocabulary id.
      if (!$this->vocabularyStorage->load($bundle)) {
        throw new \Exception(dt('Invalid vocabulary machine name: @name', ['@name' => $bundle]));
      }
    }

    $number = array_shift($args);

    if ($number === NULL) {
      $number = 10;
    }

    if (!$this->isNumber($number)) {
      throw new \Exception(dt('Invalid number of terms: @num', ['@num' => $number]));
    }

    $values = [
      'num' => $number,
      'kill' => $this->isDrush8() ? drush_get_option('kill') : $options['kill'],
      'title_length' => 12,
      'vids' => $bundles,
    ];
    $add_language = $this->isDrush8() ?
      explode(',', drush_get_option('languages', '')) :
      StringUtils::csvToArray($options['languages']);
    // Intersect with the enabled languages to make sure the language args
    // passed are actually enabled.
    $valid_languages = array_keys($this->languageManager->getLanguages(LanguageInterface::STATE_ALL));
    $values['add_language'] = array_intersect($add_language, $valid_languages);

    $translate_language = $this->isDrush8() ?
      explode(',', drush_get_option('translations', '')) :
      StringUtils::csvToArray($options['translations']);
    $values['translate_language'] = array_intersect($translate_language, $valid_languages);

    return $values;
  }

}
