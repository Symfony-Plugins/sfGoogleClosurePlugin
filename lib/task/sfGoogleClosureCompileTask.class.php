<?php

class sfGoogleClosureCompileTask extends sfGoogleClosureBaseTask
{
  
  const COMPILER_URL = 'http://code.google.com/closure/compiler/';
  
  protected function configure()
  {
    $this->namespace        = 'closure';
    $this->name             = 'compile';
    $this->briefDescription = 'Compile (optimize and pack) a JS script';
    
    $this->addOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'Application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_OPTIONAL, 'Environment', 'dev');
    
    $this->addOption('jar', 'c', sfCommandOption::PARAMETER_OPTIONAL, 'Path to compiler JAR', null);
    $this->addOption('java-bin', null, sfCommandOption::PARAMETER_OPTIONAL, 'Path to Java binary', 'java');
    
    $this->addOption('level', 'l', sfCommandOption::PARAMETER_OPTIONAL, 'Compilation level', 2);
    $this->addOption('input', 'i', sfCommandOption::PARAMETER_REQUIRED, 'Input script');
    $this->addOption('output', 'u', sfCommandOption::PARAMETER_OPTIONAL, 'Output script', 'output');
    $this->addOption('flags', 'f', sfCommandOption::PARAMETER_OPTIONAL, 'Additional flags', null);
    
    $this->addOption('no-confirmation', 'y', sfCommandOption::PARAMETER_NONE, 'Do not ask confirmation before overwriting output file');
    $this->addOption('compiler-help', 'h', sfCommandOption::PARAMETER_NONE, 'Shows help for compiler flags');
  } 
  
  protected function execute($arguments = array(), $options = array())
  {
    $cmd = $options['java-bin'];
    
    // Jar
    $jar = $options['jar'];
    if (is_null($jar))
    {
      $jar = $this->getDefaultGoogleClosureCompilerJar();
    }
    $this->validateExists('jar', $jar);
    $cmd .= ' -jar '.escapeshellarg($jar);
    
    // Show help
    if ($options['help'])
    {
      $cmd .= ' --help';
    }
    
    // Standard process
    else
    {
      // Input
      $this->validateJS('input', $options['input']);
      $cmd .= ' --js '.escapeshellarg($options['input']);
      
      // Output
      $output = $this->getJSPath($options['output']);
      if (!$options['no-confirmation'] && file_exists($output))
      {
        if (!$this->askConfirmation('Output file "'.$output.'" already exists. Overwrite ?'))
        {
          return false;
        }
      }
      $cmd .= ' --js_output_file '.escapeshellarg($output);
      
      // Level
      $cmd .= ' '.$this->getCompilationLevelFlag($options['level']);
      
      // Additional flags
      if (!is_null($options['flags']))
      {
        $cmd .= ' '.$options['flags'];
      }
    }
    
    // Execute script
    $this->getFilesystem()->sh($cmd);
  }
  
}