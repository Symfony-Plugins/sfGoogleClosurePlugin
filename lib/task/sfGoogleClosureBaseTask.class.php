<?php

abstract class sfGoogleClosureBaseTask extends sfBaseTask
{
  
  const LEVEL_FLAG = '--compilation_level';
  const LEVEL_1 = 'WHITESPACE_ONLY';
  const LEVEL_2 = 'SIMPLE_OPTIMIZATIONS';
  const LEVEL_3 = 'ADVANCED_OPTIMIZATIONS';
  
  protected $default_compilation_level = 2;
  
  protected function getCompilationLevelFlag($int_level)
  {
    if (defined('self::LEVEL_'.$int_level))
    {
      return self::LEVEL_FLAG.' '.constant('self::LEVEL_'.$int_level);
    }
    else
    {
      throw new sfException('Invalid compilation level');
    }
  }
  
  protected function convertWebPathToSystemPath($web_path, $base_dir = '')
  {
    $web_dir = sfConfig::get('sf_web_dir');
    
    if ($web_path{0} != '/')
    {
      $web_path = $base_dir . '/'.$web_path;
    }
    
    return rtrim($web_dir, DIRECTORY_SEPARATOR) . str_replace('/', DIRECTORY_SEPARATOR, $web_path);
  }
  
  protected function getDefaultGoogleClosurePath()
  {
    $web_path = sfConfig::get('app_googleClosure_base-path');
    
    return $this->convertWebPathToSystemPath($web_path, '/js');
  }
  
  protected function getDefaultGoogleClosureCompilerJar()
  {
    return str_replace('/', DIRECTORY_SEPARATOR, sfConfig::get('app_googleClosure_compiler-jar'));
  }
  
  protected function validateExists($option, $path, $type = null)
  { 
    if (is_null($type))
    {
      $type = 'file';
    }
    
    if (is_null($path))
    {
      throw new sfException('Option "'.$option.'" must be a valid ' . $type . ' : no value provided');
    }
    
    $exists = 'is_' . $type;
    if (!$exists($path))
    {
      throw new sfException('Option "'.$option.'" : ' . $type.' "'.$path.'" does not exist');
    }
  }
  
  protected function getJSPath($path)
  {
    try
    {
      $this->validateExists(null, $path);
    }
    catch (sfException $e)
    {
      if (false === strpos($path, '.'))
      {
        $path .= '.js';
      }
      $path = $this->convertWebPathToSystemPath($path, '/js');
    }
    
    return $path;
  }
  
  protected function validateJS($option, & $path)
  {
    $path = $this->getJSPath($path);
    
    $this->validateExists($option, $path, 'file');
  }
  
}
