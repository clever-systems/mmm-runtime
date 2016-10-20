<?php
/**
 * @file Runtime.php
 */

namespace clever_systems\mmm_runtime;


use clever_systems\mmm_runtime\Environment\EnvironmentInterface;
use clever_systems\mmm_runtime\Environment\Freistilbox;
use clever_systems\mmm_runtime\Environment\Platform;
use clever_systems\mmm_runtime\Environment\Uberspace;

class Runtime {
  static $environment;

  /**
   * @return EnvironmentInterface
   * @throws \Exception
   */
  public static function getEnvironment() {
    if (!static::$environment) {
      static::$environment = Freistilbox::get() || Uberspace::get() || Platform::get();
      if (!static::$environment) {
        throw new \Exception('I don\'t know this environment.');
      }
    }
    return static::$environment;
  }
}
