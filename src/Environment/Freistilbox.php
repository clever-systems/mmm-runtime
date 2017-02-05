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
  protected function fetchShortHostName() {
    return 'freistilbox';
  }

  protected function fetchUser() {
    $user = parent::fetchUser();
    if (!$user) {
      // getenv('USER') is empty on cron shell, but this is not set from server.
      $user = getenv('LOGNAME');
    }
    return $user;
  }

  protected function normalizePath($path) {
    // Only shell server has a home dir, emulate for others.
    $path = preg_replace('~^/srv/www/freistilbox/home/(s[0-9]+)/current/~',
      dirname(getcwd()) . '/', $path);
    // Make it a real path in all cases.
    return parent::normalizePath($path);
  }

  public function settings() {
    parent::settings();
    global $conf, $databases;

    $conf['file_private_path'] = "../private/$this->site";
    if (is_dir("../private") && !is_dir("../private/$this->site")) {
      mkdir("../private/$this->site");
    }
    $conf['file_temporary_path'] = "../tmp/$this->site";
    if (is_dir("../tmp") && !is_dir("../tmp/$this->site")) {
      mkdir("../tmp/$this->site");
    }

    require '../config/drupal/settings-d7-site.php';
    if (
      class_exists('Redis_Cache')
      && ($redis_options = glob('../config/drupal/settings-d7-redis*.php'))
      && count($redis_options)== 1
    ) {
      include $redis_options[0];
    }

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
