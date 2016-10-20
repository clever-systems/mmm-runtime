<?php
/**
 * @file autoDb.php
 */

namespace clever_systems\mmm_runtime\Option;

use clever_systems\mmm_runtime\Runtime;

/**
 * Class autoDb
 * @package clever_systems\mmm_runtime\Option
 */
class autoDb {
  function settings() {
    global $conf;

    // @todo Use fullname instead
    $conf['environment_indicator_overwrite'] = TRUE;
    $conf['environment_indicator_overwritten_name'] = Runtime::getEnvironment()->getLocalSiteId();
    $conf['environment_indicator_overwritten_color'] = '#'.dechex(hexdec(substr(md5($conf['environment_indicator_overwritten_name']), 0, 6)) & 0x7f7f7f); // Only dark colors.
  }
}
