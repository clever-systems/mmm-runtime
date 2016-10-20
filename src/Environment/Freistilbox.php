<?php

namespace clever_systems\mmm_runtime\Environment;

/**
 * @file Freistilbox.php
 */
class Freistilbox extends EnvironmentBase implements EnvironmentInterface {
  protected static function check() {
    return file_exists('/srv/www/freistilbox');
  }

  /**
   * @return string
   */
  protected function fetchHost() {
    return 'freistilbox';
  }

  public function setConfDefaults(&$conf) {
  }

  public function settings() {
    parent::settings();
    global $conf, $databases;

    $conf['file_private_path'] = "../private/$this->site";
    $conf['file_temporary_path'] = "../tmp/$this->site";

    require '../config/drupal/settings-d7-site.php';
    include '../config/drupal/settings-d7-redis7.php';

    if (
      // Explicit database ID.
      !empty($databases['default']['default']['database'])
      && ($database = $databases['default']['default']['database'])
    ) {
      require_once "../config/drupal/settings-d7-$database.php";
    }
    elseif (
      // Unique database ID.
      ($database_options = glob('../config/drupal/settings-d7-db*.php'))
      && count($database_options) == 1
    ) {
      require_once $database_options[0];
    }
  }
}
