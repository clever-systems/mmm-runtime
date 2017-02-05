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
  if (
       isset($alias_record['host'])
    && isset($alias_record['user'])
    && isset($alias_record['#mmm-local-host-id'])
    && $alias_record['#mmm-local-host-id'] === $environment->getLocalHostId()
  ) {
    unset($alias_record['host']);
    unset($alias_record['user']);
    $alias_record['#mmm-runtime-local'] = TRUE;
  }
}
