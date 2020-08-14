<?php

namespace Drupal\webprofiler\Csp;

/**
 * Generates Content-Security-Policy nonce.
 *
 * @author Romain Neutron <imprec@gmail.com>
 *
 * @internal
 */
class NonceGenerator {

  public function generate() {
    return bin2hex(random_bytes(16));
  }

}
