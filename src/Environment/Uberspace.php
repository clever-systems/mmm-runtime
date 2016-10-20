<?php

namespace clever_systems\mmm_runtime\Environment;

/**
 * @file Uberspace.php
 */
class Uberspace extends EnvironmentBase implements EnvironmentInterface {
  protected static function check() {
    return file_exists('/usr/local/bin/uberspace-add-domain');
  }

  /**
   * @return string
   */
  protected function fetchHost() {
    return 'uberspace';
  }

  public function settings() {
    parent::settings();
    global $conf, $databases;

    // Get DB password.
    $my_cnf = parse_ini_file(getenv('HOME') . '/.my.cnf', TRUE);
    $databases['default']['default']['password'] = $my_cnf['client']['password'];
  }
}
