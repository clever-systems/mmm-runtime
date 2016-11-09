<?php
/**
 * @file AutoBaseUrl.php
 */

namespace clever_systems\mmm_runtime\Option;

use clever_systems\mmm_runtime\Runtime;

/**
 * Class autoDb
 * @package clever_systems\mmm_runtime\Option
 */
class AutoBaseUrl {
  public static function settings($pattern = 'www.{site}.{dir}.{user}.{host}') {
    global $base_url;

    $placeholders = [
      '{user}' => Runtime::getEnvironment()->getUser(),
      '{dir}' => basename(dirname(getcwd())),
      '{site}' => Runtime::getEnvironment()->getSite(),
      '{host}' => gethostname(),
    ];

    $base_url = strtr($pattern, $placeholders);
  }
}
