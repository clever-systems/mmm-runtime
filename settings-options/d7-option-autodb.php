<?php
// Assumes variables: $mmd_installation, $mmd_sitename.

use clever_systems\mmm_runtime\Runtime;

if (!isset($mmm_database_parts)) {
  $mmm_database_parts = [];
}
$mmm_database_parts += [
  'prefix' => Runtime::getEnvironment()->getUser(),
  'installation' => dirname(dirname(getcwd())),
  'site' => Runtime::getEnvironment()->getSite(),
];
// Honor what is set already e.g. password.
if (!isset($databases['default']['default'])) {
  $databases['default']['default'] = [];
}
$databases['default']['default'] += array (
  'default' => array (
    'database' => "$mmm_database_parts[prefix]_$mmm_database_parts[installation]_$mmm_database_parts[site]",
    'driver' => 'mysql',
    'host' => 'localhost',
    'username' => 'renner',
    'prefix' => '',
  ),
);
