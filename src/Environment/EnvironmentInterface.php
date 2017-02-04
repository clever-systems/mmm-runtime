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

  public function getLocalSiteId();

  public function getLocalHostId();

  public function select($items);

  public function settings();

  public function getSite();

  public function getUser();

  public function getShortHostName();

  public function getPath();
}
