<?php
/**
 * @file mmm-runtime.drush.inc
 */
use clever_systems\mmm_runtime\Runtime;

/**
 * Implements hook_drush_sitealias_alter
 *
 * Alter alias record data in code.
 */
function mmm_runtime_drush_sitealias_alter(&$alias_record) {
  require __DIR__ . '/vendor/autoload.php';
  $environment = Runtime::getEnvironment();
  if (
       isset($alias_record['host'])
    && isset($alias_record['user'])
    && $alias_record['host'] === $environment->getHost()
    && $alias_record['user'] === $environment->getUser()
  ) {
    unset($alias_record['host']);
    unset($alias_record['user']);
    $alias_record['#mmm-runtime-local'] = TRUE;
  }
}
