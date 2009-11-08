<?php

/**
 * Include Google Closure's base script
 */
function use_javascript_closure()
{
  use_helper('Asset');
  
  use_javascript(GoogleClosureUtils::getGoogBaseJavascript());
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
  
  $args = func_get_args();
  $libraries = array();
  foreach ($args as $arg)
  {
    if (is_array($arg))
    {
      $libraries = array_merge($arg);
    }
    else
    {
      $libraries[] = $arg;
    }
  }
  
  if (count($libraries))
  {
    return javascript_tag("\n" . GoogleClosureUtils::getGoogRequire($libraries) . "\n");
  }
}

/**
 * Include one of the stylesheets embedded with Google Closure
 * 
 * @param $stylesheet
 */
function use_stylesheet_closure($stylesheet, $module = null)
{
  use_helper('Asset');
  
  use_stylesheet(GoogleClosureUtils::getGoogStylesheet($stylesheet, $module));
}
