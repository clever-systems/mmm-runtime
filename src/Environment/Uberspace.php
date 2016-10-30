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

    // Get DB password, but remove comments first.
    $ini_file = getenv('HOME') . '/.my.cnf';
    $ini_string = file_get_contents($ini_file);
    $ini_string_without_comments = preg_replace('/ *#.*$/mu', '', $ini_string);
    $my_cnf = parse_ini_string($ini_string_without_comments, TRUE, INI_SCANNER_RAW);
    $password = $my_cnf['client']['password'];
    $databases['default']['default']['password'] = $password;
  }
}
