<?php
/**
 * @file AutoDb.php
 */

namespace clever_systems\mmm_runtime\Option;

use clever_systems\mmm_runtime\Runtime;

/**
 * Class autoDb
 * @package clever_systems\mmm_runtime\Option
 */
class AutoDb {
  public static function settings($pattern = '{user}_{dir}_{site}') {
    global $databases;

    $placeholders = [
      '{user}' => Runtime::getEnvironment()->getUser(),
      '{dir}' => basename(dirname(getcwd())),
      '{site}' => Runtime::getEnvironment()->getSite(),
    ];
    $db_name = strtr($pattern, $placeholders);

    // Honor what is set already e.g. password.
    if (!isset($databases['default']['default'])) {
      $databases['default']['default'] = [];
    }
    // Filter out empty string entries.
    $databases['default']['default'] = array_filter($databases['default']['default']);

    $databases['default']['default'] += array (
      'database' => $db_name,
      'driver' => 'mysql',
      'host' => 'localhost',
      'username' => 'renner',
      'prefix' => '',
    );
  }
}
