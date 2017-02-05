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
  if (defined('DRUPAL_ROOT')) {
    @include DRUPAL_ROOT . '/../vendor/autoload.php';
  }

  $environment = Runtime::getEnvironment();
  $local_host_id = $environment->getLocalHostId();
  if (
       isset($alias_record['remote-host'])
    && isset($alias_record['remote-user'])
    && isset($alias_record['#mmm-local-host-id'])
    && $alias_record['#mmm-local-host-id'] === $local_host_id
  ) {
    unset($alias_record['remote-host']);
    unset($alias_record['remote-user']);
    $alias_record['#mmm-runtime-local'] = TRUE;
  }
}
