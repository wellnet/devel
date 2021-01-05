<?php

namespace Drupal\webprofiler\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * From controller to set Webprofiler settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The Profiler service.
   *
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  private $profiler;

  /**
   * A list of registered data collector templates.
   *
   * @var array
   */
  private $templates;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);

    $instance->profiler = $container->get('webprofiler.profiler');
    $instance->templates = $container->getParameter('webprofiler.templates');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webprofiler_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'webprofiler.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('webprofiler.settings');

    $form['purge_on_cache_clear'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Purge on cache clear'),
      '#description' => $this->t('Deletes all profiler files during cache clear.'),
      '#default_value' => $config->get('purge_on_cache_clear'),
    ];

    $form['exclude_paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Exclude paths'),
      '#default_value' => $config->get('exclude_paths'),
      '#description' => $this->t('Paths to exclude for profiling. One path per line.'),
    ];

    $form['active_toolbar_items'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Active toolbar items'),
      '#options' => $this->getCollectors(),
      '#description' => $this->t('Choose which items to show into the toolbar.'),
      '#default_value' => $config->get('active_toolbar_items'),
    ];

    $form['ide_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('IDE settings'),
      '#open' => FALSE,
    ];

    $form['ide_settings']['ide'] = [
      '#type' => 'select',
      '#title' => $this->t('IDE'),
      '#options' => $this->getIdes(),
      '#description' => $this->t('IDE URL template for open files.'),
      '#default_value' => $config->get('ide'),
    ];

    $form['ide_settings']['ide_remote_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IDE link remote path'),
      '#description' => $this->t('The path of the remote docroot. Leave blank if the docroot is on the same machine of the IDE.'),
      '#default_value' => $config->get('ide_remote_path'),
    ];

    $form['ide_settings']['ide_local_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IDE link local path'),
      '#description' => $this->t('The path of the local docroot. Leave blank if the docroot is on the same machine of IDE.'),
      '#default_value' => $config->get('ide_local_path'),
    ];

    $form['database'] = [
      '#type' => 'details',
      '#title' => $this->t('Database settings'),
      '#open' => FALSE,
      '#states' => [
        'visible' => [
          [
            'input[name="active_toolbar_items[database]"]' => ['checked' => TRUE],
          ],
        ],
      ],
    ];

    $form['database']['query_sort'] = [
      '#type' => 'radios',
      '#title' => $this->t('Sort query log'),
      '#options' => ['source' => $this->t('by source'), 'duration' => $this->t('by duration')],
      '#description' => $this->t('The query table can be sorted in the order that the queries were executed or by descending duration.'),
      '#default_value' => $config->get('query_sort'),
    ];

    $form['database']['query_highlight'] = [
      '#type' => 'number',
      '#title' => $this->t('Slow query highlighting'),
      '#description' => $this->t('Enter an integer in milliseconds. Any query which takes longer than this many milliseconds will be highlighted in the query log. This indicates a possibly inefficient query, or a candidate for caching.'),
      '#default_value' => $config->get('query_highlight'),
      '#min' => 0,
    ];

    $form['purge'] = [
      '#type' => 'details',
      '#title' => $this->t('Purge profiles'),
      '#open' => FALSE,
    ];

    $form['purge']['actions'] = ['#type' => 'actions'];
    $form['purge']['actions']['purge'] = [
      '#type' => 'submit',
      '#value' => $this->t('Purge'),
      '#submit' => [[$this, 'purge']],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('webprofiler.settings')
      ->set('purge_on_cache_clear', $form_state->getValue('purge_on_cache_clear'))
      ->set('exclude_paths', $form_state->getValue('exclude_paths'))
      ->set('active_toolbar_items', $form_state->getValue('active_toolbar_items'))
      ->set('ide', $form_state->getValue('ide'))
      ->set('ide_remote_path', $form_state->getValue('ide_remote_path'))
      ->set('ide_local_path', $form_state->getValue('ide_local_path'))
      ->set('query_sort', $form_state->getValue('query_sort'))
      ->set('query_highlight', $form_state->getValue('query_highlight'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Purges profiles.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function purge(array &$form, FormStateInterface $form_state) {
    $this->profiler->purge();
    $this->messenger()->addMessage($this->t('Profiles purged'));
  }

  /**
   * Return a list of defined collectors.
   *
   * @return array
   *   A list of defined collectors.
   */
  private function getCollectors() {
    $options = [];
    foreach ($this->templates as $template) {
      // Drupal collector should not be disabled.
      if ($template[0] != 'drupal') {
        $options[$template[0]] = $template[2];
      }
    }

    asort($options);

    return $options;
  }

  /**
   * Return a list of IDE URL template for open files.
   *
   * @return array
   *   A list of IDE URL template for open files.
   */
  private function getIdes() {
    return [
      'txmt://open?url=file://%f&line=%l' => 'textmate',
      'mvim://open?url=file://%f&line=%l' => 'macvim',
      'emacs://open?url=file://%f&line=%l' => 'emacs',
      'subl://open?url=file://%f&line=%l' => 'sublime',
      'phpstorm://open?file=%f&line=%l' => 'phpstorm',
      'atom://core/open/file?filename=%f&line=%l' => 'atom',
      'vscode://file/%f:%l' => 'vscode',
    ];
  }

}
