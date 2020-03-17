<?php

/**
 * @file
 */

/**
 * Wrapping function for invoking the right
 * method responsible for handle a given batch operation.
 * Available for every DevelGeneratePlugin.
 */
function devel_generate_operation(Drupal\devel_generate\DevelGenerateBaseInterface $class, $method, $vars, &$context) {
  return $class->$method($vars, $context);
}

/**
 * Common finish batch function available for
 * every DevelGeneratePlugin.
 */
function devel_generate_batch_finished($success, $results, $operations) {

  if ($success) {
    if (!empty($results['num_translations'])) {
      $message = t('Finished @num elements and @num_translations translations created successfully.', ['@num' => $results['num'], '@num_translations' => $results['num_translations']]);
    }
    else {
      $message = t('Finished @num elements created successfully.', ['@num' => $results['num']]);
    }
  }
  else {
    $message = t('Finished with an error.');
  }
  \Drupal::messenger()->addMessage($message);
}
