<?php

namespace Drupal\webprofiler\Csp;

/**
 * Generates Content-Security-Policy nonce.
 *
 * @internal
 */
class NonceGenerator {

  /**
   * Generates Content-Security-Policy nonce.
   *
   * @return string
   *   A nonce.
   *
   * @throws \Exception
   */
  public function generate() {
    return bin2hex(random_bytes(16));
  }

}
