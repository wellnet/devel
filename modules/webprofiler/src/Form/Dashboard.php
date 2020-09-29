<?php

namespace Drupal\webprofiler\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Class Dashboard.
 */
class Dashboard extends FormBase {

  /**
   * The Profiler service.
   *
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  private $profiler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('webprofiler.profiler')
    );
  }

  /**
   *
   */
  public function __construct(Profiler $profiler) {
    $this->profiler = $profiler;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webprofiler_dashboard';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->profiler->disable();

    $openPanel = $this->getRequest()->get('panel', 'drupal');
    $token = $this->getRequest()->get('token');

    $form['collectors'] = [
      '#type' => 'vertical_tabs',
    ];

    $collectors = $this->profiler->all();
    /** @var \Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface $collector */
    foreach ($collectors as $collector) {
      $name = $collector->getName();
      $form[$name] = [
        '#type' => 'details',
        '#title' => $name,
        '#group' => 'collectors',
      ];

      if ($openPanel === $name) {
        $form[$name]['#open'] = TRUE;
      }

      $form[$name]['panel'] = [
        '#theme' => 'webprofiler_panel_js',
        '#name' => $name,
        '#token' => $token,
      ];
    }

    $form['#attached'] = [
      'library' => ['webprofiler/dashboard'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Nothing to submit here.
  }

}
