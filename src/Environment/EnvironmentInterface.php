<?php
/**
 * @file EnvironmentInterface.php
 */
namespace clever_systems\mmm_runtime\Environment;


/**
 * @file Freistilbox.php
 */
interface EnvironmentInterface {
  /**
   * Static constructor after check.
   *
   * @return null|static
   */
  public static function get();

  /**
   * @return string
   */
  public function getLocalSiteId();

  /**
   * @return string
   */
  public function getLocalHostId();

  /**
   * @param mixed[] $site_urn_items
   * @return mixed
   */
  public function select($site_urn_items);

  /**
   * @param string $site_urn
   * @return bool
   */
  public function match($site_urn);

  /**
   * @return void
   */
  public function settings();

  /**
   * @return string
   */
  public function getSite();

  /**
   * @return string
   */
  public function getUser();

  /**
   * @return string
   */
  public function getShortHostName();

  /**
   * @return string
   */
  public function getPath();
}
