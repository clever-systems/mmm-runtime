<?php

namespace clever_systems\mmm_runtime;

class Helper {

  /**
   * @param string $file
   * @param string $variable
   * @param array|null $keys
   * @return array
   */
  public static function filterInclude($file, $variable, array $keys = NULL) {
    @include $file;
    $return = $$variable;
    if (isset($keys)) {
      $return = array_intersect_key($return, array_fill_keys($keys, TRUE));
    }
    return $return;
  }
}
