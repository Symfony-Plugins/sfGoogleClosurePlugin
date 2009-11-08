<?php

class GoogleClosureUtils
{
  
  /**
   * Get the "goog.require" instructions for provided libraries
   * 
   * @param $libraries
   * @param $separator
   * @return string
   */
  static public function getGoogRequire(array $libraries, $separator = "\n")
  {
    return 'goog.require("' . implode('");' . $separator . 'goog.require("', $libraries) .  '");';
  }
  
  /**
   * Convert a web path into a system path (useful to check if file exists)
   * 
   * @param $web_path
   * @param $base_dir
   * @return string
   */
  static public function convertWebPathToSystemPath($web_path, $base_dir = '/js')
  {
    $web_dir = sfConfig::get('sf_web_dir');
    
    if ($web_path{0} != '/')
    {
      $web_path = $base_dir . '/'.$web_path;
    }
    
    return rtrim($web_dir, DIRECTORY_SEPARATOR) . str_replace('/', DIRECTORY_SEPARATOR, $web_path);
  }
  
  /**
   * Returns the web path to Google Closure library
   * 
   * @return string
   */
  static public function getGoogBasePath()
  {
    $base_path = sfConfig::get('app_googleClosure_base-path');
    
    return rtrim($base_path, '/');
  }
  
  /**
   * Get the web path to an embedded demo stylesheet
   * 
   * @param $stylesheet
   * @return string
   */
  static public function getGoogStylesheet($stylesheet, $module = null)
  {
    $base_path = self::getGoogBasePath();
    
    if (false === strpos($stylesheet, '.'))
    {
      $stylesheet .= '.css';
    }

    $path = $base_path . '/goog/demos/';
    if (!is_null($module))
    {
      $path .= $module . '/';
    }
    
    return $path . 'css/' . $stylesheet;
  }
  
  /**
   * web path to Google Closure's base.je
   * 
   * @return string
   */
  static public function getGoogBaseJavascript()
  {
    return self::getGoogBasePath() . '/goog/base.js';
  }
  
}
