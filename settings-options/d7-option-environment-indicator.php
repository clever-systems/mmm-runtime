<?php
/**
 * @file settings.common.ei.php
 */

use clever_systems\mmm_runtime\Runtime;

// @todo Use fullname instead
$conf['environment_indicator_overwrite'] = TRUE;
$conf['environment_indicator_overwritten_name'] = Runtime::getEnvironment()->getLocalSiteId();
$conf['environment_indicator_overwritten_color'] = '#'.dechex(hexdec(substr(md5($conf['environment_indicator_overwritten_name']), 0, 6)) & 0x7f7f7f); // Only dark colors.
