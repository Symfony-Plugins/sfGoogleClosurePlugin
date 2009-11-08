<?php

/**
 * Include Google Closure's base script
 */
function use_javascript_closure()
{
  use_helper('Asset');
  
  $base_path = sfConfig::get('app_googleClosure_base-path');
  $base_path = rtrim($base_path, '/');
  
  use_javascript($base_path . '/goog/base.js');
}

/**
 * Require a Google Closure's library
 * 
 * @param string $library
 * @return string
 */
function goog_require($library)
{
  use_helper('Javascript');
  
  use_javascript_closure();
  
  return javascript_tag('goog.require(\'' . $library .  '\');');
}
