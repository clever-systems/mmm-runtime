<?php
/**
 * @file Redis.php
 */

namespace clever_systems\mmm_runtime\Option;
use clever_systems\mmm_runtime\Runtime;

/**
 * Class redis
 * @package clever_systems\mmm_runtime\Option
 */
class Redis {
  public static function settings() {
    global $conf;

    if (!empty($conf['redis_client_host'])) {
      $conf['lock_inc']               = 'sites/all/modules/redis/redis.lock.inc';
      $conf['path_inc']               = 'sites/all/modules/redis/redis.path.inc';
      $conf['cache_backends'][]       = 'sites/all/modules/redis/redis.autoload.inc';
      $conf['cache_default_class']    = 'Redis_Cache';
    }

    $conf['cache_prefix']['default'] = Runtime::getEnvironment()->getLocalSiteId();
  }
}
