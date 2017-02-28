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
  protected function fetchShortHostName() {
    return 'uberspace';
  }

  public function settings(&$settings, &$databases) {
    parent::settings($settings, $databases);
    $is_d8 = $this->drupal_major_version == '8';

    if (empty($databases['default']['default']['password'])) {
      // Get DB password, but remove comments first.
      $ini_file = getenv('HOME') . $is_d8 ? '/.my.mariadb.cnf' : '/.my.cnf';
      $ini_string = file_get_contents($ini_file);
      $ini_string_without_comments = preg_replace('/ *#.*$/mu', '', $ini_string);
      $my_cnf = parse_ini_string($ini_string_without_comments, TRUE, INI_SCANNER_RAW);
      $password = $my_cnf['client']['password'];
      $databases['default']['default']['password'] = $password;
    }
    // Add defaults
    if (isset($databases['default']['default']['database'])) {
      $databases['default']['default'] += [
        'driver' => 'mysql',
        'username' => $this->getUser(),
        'host' => $is_d8 ? '127.0.0.1' : 'localhost',
        'port' => $is_d8 ? 3307 : 3306,
        'prefix' => '',
      ];
    }
  }

}
