<?php
/**
 * @file EnvironmentBase.php
 */

namespace clever_systems\mmm_runtime\Environment;


/**
 * Class EnvironmentBase
 * @package clever_systems\mmm\Environment
 *
 * @todo Consider splitting to value/matcher, checker, settings-provider.
 */
abstract class EnvironmentBase implements EnvironmentInterface {
  /** @var string */
  protected $user;
  /** @var string */
  protected $host;
  /** @var string */
  protected $site;
  /** @var string */
  protected $path;

  /**
   * Static constructor after check.
   * 
   * @return null|static
   */
  public static function get() {
    if (static::check()) {
      return new static();
    }
    return NULL;
  }

  /**
   * Check if this environment class matches.
   * 
   * @return bool
   */
  protected static function check() {
    return FALSE;
  }

  /**
   * Freistilbox constructor.
   * @param string $site
   */
  public function __construct() {
    $this->user = $this->fetchUser();
    $this->host = $this->fetchHost();
    $this->path = $this->fetchPath();
    $this->site = $this->fetchSite();
  }

  public function getLocalSiteId() {
    return "$this->user@$this->host$this->path#$this->site";
  }

  public function select($items) {
    foreach ($items as $pattern => $item) {
      if ($this->match($pattern)) {
        return $item;
      }
    }
    return NULL;
  }

  /**
   * Match a pattern against current environment.
   *
   * @param string $pattern
   * @return bool
   */
  protected function match($pattern) {
    $pattern_parts = parse_url($pattern);
    if (
      isset($pattern_parts['path'])
      // Adjust path,
      && $pattern_parts['path'] = $this->realpath($pattern_parts['path'])
        // and quit if it does not exist.
        && $pattern_parts['path'] === FALSE
    ) {
      return FALSE;
    }
    $relevant_environment_parts = array_intersect_key($this->getParts(), $pattern_parts);
    return $pattern_parts = $relevant_environment_parts;
  }

  /**
   * @return array
   */
  protected function getParts() {
    return [
      'user' => $this->user,
      'host' => $this->host,
      'path' => $this->path,
      'fragment' => $this->site,
    ];
  }

  /**
   * Get real path, replcing ~.
   *
   * @param $path
   * @return string
   */
  protected function realpath($path) {
    $path = preg_replace('#^/?~#u', $this->fetchHomePath(), $path);
    return realpath($path);
  }

  /**
   * Return the user's home directory.
   *
   * Copied from drush_server_home().
   */
  protected function fetchHomePath() {
    // Cannot use $_SERVER superglobal since that's empty during UnitUnishTestCase
    // getenv('HOME') isn't set on Windows and generates a Notice.
    $home = getenv('HOME');
    if (!empty($home)) {
      // home should never end with a trailing slash.
      $home = rtrim($home, '/');
    }
    elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
      // home on windows
      $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
      // If HOMEPATH is a root directory the path can end with a slash. Make sure
      // that doesn't happen.
      $home = rtrim($home, '\\/');
    }
    return empty($home) ? NULL : $home;
  }

  /**
   * @return string
   */
  protected function fetchUser() {
    return get_current_user();
  }

  /**
   * @return string
   */
  protected function fetchPath() {
    return getcwd();
  }

  /**
   * Fetch current site.
   *
   * @return string
   */
  protected function fetchSite() {
    if (function_exists('conf_path')) {
      // D7
      $conf_path = conf_path();
    }
    else {
      // D8
      $conf_path = \Drupal\Core\DrupalKernel ::findSitePath(\Symfony\Component\HttpFoundation\Request ::createFromGlobals());
    }
    $site = basename($conf_path);
    return $site;
  }

  /**
   * @return string
   */
  abstract protected function fetchHost();

  public function settings() {
    global $conf;
    // Lock public file path against erroneous variable deploys.
    $conf['file_public_path'] = "sites/$this->site/files";
  }

  /**
   * @return string
   */
  public function getSite() {
    return $this->site;
  }

  /**
   * @return string
   */
  public function getUser() {
    return $this->user;
  }

  /**
   * @return string
   */
  public function getHost() {
    return $this->host;
  }

  /**
   * @return string
   */
  public function getPath() {
    return $this->path;
  }

}
