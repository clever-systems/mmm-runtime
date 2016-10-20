<?php
/**
 * Redis default configuration.
 */
$conf['redis_client_interface'] = 'PhpRedis';
$conf['lock_inc']               = 'sites/all/modules/redis/redis.lock.inc';
$conf['path_inc']               = 'sites/all/modules/redis/redis.path.inc';
$conf['cache_backends'][]       = 'sites/all/modules/redis/redis.autoload.inc';
$conf['cache_default_class']    = 'Redis_Cache';

