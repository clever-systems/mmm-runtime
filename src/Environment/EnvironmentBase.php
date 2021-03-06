<?php
/**
 * @file EnvironmentBase.php
 */

namespace clever_systems\mmm_runtime\Environment {


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
    protected $short_host_name;
    /** @var string */
    protected $site;
    /** @var string */
    protected $path;
    /** @var int|null */
    protected $drupal_major_version;

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
     */
    public function __construct() {
      // Do it first, as $this->fetchsite() needs it.
      if (defined('DRUPAL_ROOT')) {
        $this->drupal_major_version = file_exists(DRUPAL_ROOT . '/core/lib/Drupal.php')
          ? 8 : 7;
      }

      $this->user = $this->fetchUser();
      $this->short_host_name = $this->fetchShortHostName();
      $this->path = $this->fetchPath();
      $this->site = $this->fetchSite();
    }

    /**
     * @return string
     */
    public function getHostUrn() {
      return "$this->user@$this->short_host_name";
    }

    /**
     * @return string
     */
    public function getSiteUrn() {
      $local_host_id = $this->getLocalHostId();
      return "$local_host_id$this->path#$this->site";
    }

    protected function buildSiteUrn($parts) {
      return "$parts[user]@$parts[host]$parts[path]#$parts[fragment]";
    }

    /**
     * @return string
     */
    public function getNormalizedSiteUrn() {
      $normalized_site_urn = $this->buildSiteUrn(
        $this->normalizePathInSiteUrnParts($this->getSiteUrnParts()));
      return $normalized_site_urn;
    }

    /**
     * @deprecated Use getHostUrn()
     *
     * @return string
     */
    public function getLocalSiteId() {
      return $this->getSiteUrn();
    }

    /**
     * @deprecated Use getHostUrn()
     *
     * @return string
     */
    public function getLocalHostId() {
      return $this->getHostUrn();
    }


    public function select($site_urn_items) {
      foreach ($site_urn_items as $site_urn => $item) {
        if ($this->match($site_urn)) {
          return $item;
        }
      }
      return NULL;
    }

    /**
     * Match a pattern against current environment.
     *
     * @param string $site_urn
     * @return bool
     */
    public function match($site_urn) {
      $site_urn_parts = $this->normalizePathInSiteUrnParts($this->parseSiteUrnParts($site_urn));
      $current_site_urn_parts = $this->normalizePathInSiteUrnParts($this->getSiteUrnParts());
      $relevant_site_urn_parts = array_intersect_key($current_site_urn_parts, $site_urn_parts);
      $matching = $site_urn_parts == $relevant_site_urn_parts;
      return $matching;
    }

    /**
     * @internal Public only for debugging.
     * @param string $pattern
     * @return string[]
     */
    public function parseSiteUrnParts($pattern) {
      // parse_url needs schema, so add and remove a dummy.
      $pattern_parts = parse_url("dummy://$pattern");
      unset($pattern_parts['scheme']);
      return $pattern_parts;
    }

    /**
     * @internal Public only for debugging.
     * @param string[] $pattern_parts
     * @return string[]
     */
    public function normalizePathInSiteUrnParts($pattern_parts) {
      // Adjust path,
      if (isset($pattern_parts['path'])) {
        $pattern_parts['path'] = $this->normalizePath($pattern_parts['path']);
        return $pattern_parts;
      }
      return $pattern_parts;
    }

    /**
     * @internal Public only for debugging.
     * @return string[]
     */
    public function getSiteUrnParts() {
      return [
        'user' => $this->user,
        'host' => $this->short_host_name,
        'path' => $this->path,
        'fragment' => $this->site,
      ];
    }

    /**
     * Get real path, replcing ~.
     *
     * @param string $path
     * @return string
     */
    protected function normalizePath($path) {
      return realpath($path);
    }

    /**
     * Return the user's home directory.
     *
     * Copied from drush_server_home().
     *
     * @return string
     */
    protected function fetchHomePath() {
      // Cannot use $_SERVER superglobal since that's empty during (what?)
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
      // get_current_user() is 'root' e.g. on freistilbox shell.
      return getenv('USER');
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
      $site = '';
      if ($this->drupal_major_version == 7) {
        $conf_path = conf_path();
        $site = basename($conf_path);
      }
      elseif ($this->drupal_major_version == 8) {
        $conf_path = \Drupal\Core\DrupalKernel::findSitePath(\Symfony\Component\HttpFoundation\Request::createFromGlobals());
        $site = basename($conf_path);
      }
      return $site;
    }

    /**
     * @return string
     */
    abstract protected function fetchShortHostName();

    /**
     * Adjust settings.
     *
     * For D7, see \drupal_settings_initialize
     *   Runtime::getEnvironment()->settings($conf, $databases);
     *
     * For D8, see \Drupal\Core\Site\Settings::initialize
     *   Runtime::getEnvironment()->settings($settings, $databases);
     *
     * Pass some vars by reference, as in D8 some are no longer globals.
     *
     * @param $settings
     *   Note that in D7, $settings is called $conf.
     * @param $databases
     *   DB credentials, all versions.
     */
    public function settings(&$settings, &$databases) {
      // Lock public file path against erroneous variable deploys.
      $settings['file_public_path'] = "sites/$this->site/files";
      $private_path = "../private/$this->site";
      if (!file_exists($private_path)) {
        mkdir($private_path);
      }
      $settings['file_private_path'] = $private_path;

      // Set standard config sync directory.
      $tmp_path = "../tmp/$this->site";
      if (!file_exists($tmp_path)) {
        mkdir($tmp_path);
      }
      if ($this->drupal_major_version == 8) {
        global $config_directories;
        $config_directories[CONFIG_SYNC_DIRECTORY] = '../config-sync';

        global $config;
        $config['system.file']['path']['temporary'] = $tmp_path;
      }
      else {
        $settings['file_temporary_path'] = $tmp_path;

        $settings['environment_indicator_overwrite'] = TRUE;
        $settings['environment_indicator_overwritten_name'] = !empty($settings['mmm']['installation']) ? $settings['mmm']['installation'] : $this->getHostUrn();
        $settings['environment_indicator_overwritten_color'] = '#'.dechex(hexdec(substr(md5($settings['environment_indicator_overwritten_name']), 0, 6)) & 0x7f7f7f); // Only dark colors.
        $settings['environment_indicator_overwritten_text_color'] = '#ffffff';
      }

      // Assumes possibly many installation per user. Override if else.
      $settings['cache_prefix']['default'] = "$this->user:$this->path:$this->site";
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
    public function getShortHostName() {
      return $this->short_host_name;
    }

    /**
     * @return string
     */
    public function getPath() {
      return $this->path;
    }

  }

}