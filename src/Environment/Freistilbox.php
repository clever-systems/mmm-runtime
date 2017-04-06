<?php

namespace clever_systems\mmm_runtime\Environment;
use clever_systems\mmm_runtime\Helper;

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

  public function settings(&$settings, &$databases) {
    parent::settings($settings, $databases);
    $version = $this->drupal_major_version;
    $settings_variable = ($version == 7) ? 'conf' : 'settings';

    $settings['file_private_path'] = "../private/$this->site";
    if (is_dir("../private") && !is_dir("../private/$this->site")) {
      mkdir("../private/$this->site");
    }
    $settings['file_temporary_path'] = "../tmp/$this->site";
    if (is_dir("../tmp") && !is_dir("../tmp/$this->site")) {
      mkdir("../tmp/$this->site");
    }

    require "../config/drupal/settings-d$version-site.php";

    // REDIS:
    // Get unique redis credentials.
    // We can neither rely on module_exists here (it's too early) nor use
    // class_exists (as cache classes are included via $conf['cache_backends'])
    // nor check that setting (as this runs before user settings).
    // So we just always include the config snippet.
    // Also we don NOT want the snippet to set $settings['cache']['default']
    // and $settings['cache_prefix']['default'] so we filter the include.
    if (
      ($redis_options = glob("../config/drupal/settings-d$version-redis*.php"))
      && count($redis_options) == 1
    ) {
      $keys = ($version == 7) ?
        ['redis_client_host', 'redis_client_port', 'redis_client_password'] :
        ['redis.connection'];
      $settings = Helper::filterInclude($redis_options[0], $settings_variable, $keys) + $settings;
    }

    // DATABASE:
    if (
      // Explicit database ID.
      !empty($databases['default']['default']['database'])
      && ($database = $databases['default']['default']['database'])
    ) {
      require_once "../config/drupal/settings-d$version-$database.php";
    }
    elseif (
      // Unique database ID.
      ($database_options = glob("../config/drupal/settings-d$version-db*.php"))
      && count($database_options) == 1
    ) {
      require_once $database_options[0];
    }

    // Only one installation per user possible.
    $settings['cache_prefix']['default'] = "$this->user:$this->site";
  }

}
