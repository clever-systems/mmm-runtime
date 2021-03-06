<?php
/**
 * @file Runtime.php
 */

namespace clever_systems\mmm_runtime;


use clever_systems\mmm_runtime\Environment\EnvironmentInterface;
use clever_systems\mmm_runtime\Environment\Freistilbox;
use clever_systems\mmm_runtime\Environment\Platform;
use clever_systems\mmm_runtime\Environment\Uberspace;

/**
 * Class Runtime
 * @package clever_systems\mmm_runtime
 *
 * Usage: @see \clever_systems\mmm\Compiler
 */
class Runtime {
  /** @var EnvironmentInterface */
  static $environment;

  /**
   * @return EnvironmentInterface
   * @throws \Exception
   */
  public static function getEnvironment() {
    // If environment is created in alias handling, it does not carry a site.
    // Recreate it in this case.
    // @todo Implement a better architecture to prevent this hack.
    if (!static::$environment || !static::$environment->getSite()) {
      static::$environment = Freistilbox::get() ?: Uberspace::get() ?: Platform::get();
      if (!static::$environment) {
        throw new \Exception('I don\'t know this environment.');
      }
    }
    return static::$environment;
  }
}
