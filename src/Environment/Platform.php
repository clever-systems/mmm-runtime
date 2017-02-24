<?php

namespace clever_systems\mmm_runtime\Environment;

class Platform extends EnvironmentBase implements EnvironmentInterface {
  protected static function check() {
    return isset($_SERVER['PLATFORM_RELATIONSHIPS']);
  }

  /**
   * @return string
   */
  protected function fetchShortHostName() {
    return 'platform';
  }

  /**
   * @file Platform.php
   */
  public function settings(&$settings, &$databases, &$config = NULL, &$config_directories = NULL) {
    parent::settings();

    // Configure private and temporary file paths.
    if (isset($_ENV['PLATFORM_APP_DIR'])) {
      if (!isset($settings['file_private_path'])) {
        $settings['file_private_path'] = $_ENV['PLATFORM_APP_DIR'] . '/private';
      }
      if (!isset($settings['file_temporary_path'])) {
        $settings['file_temporary_path'] = $_ENV['PLATFORM_APP_DIR'] . '/tmp';
      }
    }

    // Import variables prefixed with 'drupal:' into $conf.
    if (isset($_ENV['PLATFORM_VARIABLES'])) {
      $variables = json_decode(base64_decode($_ENV['PLATFORM_VARIABLES']), TRUE);

      $prefix_len = strlen('drupal:');
      $drupal_globals = array('cookie_domain', 'installed_profile', 'drupal_hash_salt', 'base_url');
      foreach ($variables as $name => $value) {
        if (substr($name, 0, $prefix_len) == 'drupal:') {
          $name = substr($name, $prefix_len);
          if (in_array($name, $drupal_globals)) {
            $GLOBALS[$name] = $value;
          }
          else {
            $settings[$name] = $value;
          }
        }
      }
    }

    // Default PHP settings.
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    ini_set('session.gc_maxlifetime', 200000);
    ini_set('session.cookie_lifetime', 2000000);
    ini_set('pcre.backtrack_limit', 200000);
    ini_set('pcre.recursion_limit', 200000);

    // Force Drupal not to check for HTTP connectivity until we fixed the self test.
    $settings['drupal_http_request_fails'] = FALSE;

    if (isset($_ENV['PLATFORM_RELATIONSHIPS'])) {
      $relationships = json_decode(base64_decode($_ENV['PLATFORM_RELATIONSHIPS']), TRUE);
    }

    if (!empty($relationships['redis'])) {
      $redis = $relationships['redis'][0];
      $settings['redis_client_host']      = $redis['host'];
      $settings['redis_client_port']      = $redis['port'];
      // Unique prefix:
      $settings['cache_prefix']['default'] = $this->site;
    }

    if (
      // Explicit database ID.
    !empty($databases['default']['default']['database'])
    ) {
      $database = $databases['default']['default']['database'];
    }
    elseif (
      // Unique database.
      ($db_relationship_keys = preg_grep('/^database/', array_keys($relationships)))
      && count($db_relationship_keys) === 1
    ) {
      $database = $db_relationship_keys[0];
    }

    if (isset($database)) {
      // === Start taken from platform local settings ===
      if (!empty($relationships[$database])) {
        foreach ($relationships[$database] as $endpoint) {
          $database = array(
            'driver' => $endpoint['scheme'],
            'database' => $endpoint['path'],
            'username' => $endpoint['username'],
            'password' => $endpoint['password'],
            'host' => $endpoint['host'],
            'port' => $endpoint['port'],
          );

          if (!empty($endpoint['query']['compression'])) {
            $database['pdo'][PDO::MYSQL_ATTR_COMPRESS] = TRUE;
          }

          if (!empty($endpoint['query']['is_master'])) {
            $databases['default']['default'] = $database;
          }
          else {
            $databases['default']['slave'][] = $database;
          }
        }
      }
      // === End taken from platform local settings ===
    }
  }

}
