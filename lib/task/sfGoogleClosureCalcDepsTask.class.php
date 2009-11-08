<?php

class sfGoogleClosureCalcDepsTask extends sfGoogleClosureBaseTask
{

  const MODE_LIST = 'list';
  const MODE_SCRIPT = 'script';
  const MODE_DEPS = 'deps';
  const MODE_COMPILED = 'compiled';
  
  protected $output_modes = array(self::MODE_LIST, self::MODE_SCRIPT, self::MODE_DEPS, self::MODE_COMPILED);
  protected $default_output_mode = self::MODE_SCRIPT;
  
  protected function configure()
  {
    $this->namespace        = 'closure';
    $this->name             = 'calc-deps';
    $this->briefDescription = 'Calculate dependencies of a Google Closure script';
    
    $this->addOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'Application', 'frontend');
    $this->addOption('env', null, sfCommandOption::PARAMETER_OPTIONAL, 'Environment', 'dev');
    
    $this->addOption('script', null, sfCommandOption::PARAMETER_OPTIONAL, 'Path to calc-deps.py', null);
    $this->addOption('python-bin', null, sfCommandOption::PARAMETER_OPTIONAL, 'Path to Python binary', 'python');
    
    $this->addOption('input', 'i', sfCommandOption::PARAMETER_OPTIONAL, 'Input script');
    $this->addOption('output', 'u', sfCommandOption::PARAMETER_OPTIONAL, 'Output script');
    $this->addOption('output-mode', 'o', sfCommandOption::PARAMETER_OPTIONAL, 'Output mode, can be one of "'.implode(", ", $this->output_modes).'"', $this->default_output_mode);
    $this->addOption('path', 'p', sfCommandOption::PARAMETER_OPTIONAL, 'Paths to be traversed to build dependencies', null);
    $this->addOption('compiler-jar', 'c', sfCommandOption::PARAMETER_OPTIONAL, 'Path to compiler JAR', null);
    $this->addOption('compiler-flags', 'f', sfCommandOption::PARAMETER_OPTIONAL, 'Additional flags passed to the Closure compiler', null);
    $this->addOption('compiler-level', 'l', sfCommandOption::PARAMETER_OPTIONAL, 'Compilation level : from 1 to 3', $this->default_compilation_level);
    
    $this->addOption('no-confirmation', 'y', sfCommandOption::PARAMETER_NONE, 'Do not ask confirmation before overwriting output file');
  } 
  
  protected function getDefaultGoogleClosureCalcDepsScript()
  {
    return str_replace('/', DIRECTORY_SEPARATOR, sfConfig::get('app_googleClosure_calc-deps-py'));
  }
  
  protected function validateOutputMode($mode)
  {
    if (!in_array($mode, $this->output_modes))
    {
      throw new sfException('Input mode must be one of "'.implode('", "', $this->output_modes).'"');
    }
  }
  
  protected function execute($arguments = array(), $options = array())
  {
    $mode = $options['output-mode'];
    $this->validateOutputMode($mode);
    
    $script = is_null($options['script']) ? $this->getDefaultGoogleClosureCalcDepsScript() : $options['script'];
    $this->validateExists('script', $script, 'file');
    
    $base_path = is_null($options['path']) ? $this->getDefaultGoogleClosurePath() : $options['path'];
    $this->validateExists('path', $base_path, 'dir');
    
    $command_bin = $options['python-bin'] . ' ' . escapeshellarg($script);
    $command_args = array(
      '-o' => $mode,
      '-p' => $base_path,
    );
    
    // Modes "script", "compiled", and "list" requires an input script
    if ($mode != self::MODE_DEPS)
    {
      if (is_null($options['input']))
      {
        throw new sfException('Input script (option "input") is mandatory for mode "'.$mode.'"');
      }
      $this->validateJS('input', $options['input']);
      $command_args['-i'] = $options['input'];
    }
    
    // Mode "compiled" requires handling compiler options
    if ($mode == self::MODE_COMPILED)
    {
      // compiler-jar
      $jar = $options['compiler-jar'];
      if (is_null($jar))
      {
        $jar = $this->getDefaultGoogleClosureCompilerJar();
      }
      $this->validateExists('compiler-jar', $jar);
      $command_args['-c'] = $jar;
      // compiler-level and flags
      $level = $options['compiler-level'];
      $flags = $options['compiler-flags'];
      if ($level_flag = $this->getCompilationLevelFlag($level))
      {
        $flags = $level_flag . ' ' . $flags;
      }
      $command_args['-f'] = $flags;
    }
    
    // Check output file if provided
    $output = $options['output'];
    if (is_null($output))
    {
      $output = $mode == self::MODE_DEPS ? 'deps' : 'output';
    }
    $output = $this->getJSPath($output);
    if (!$options['no-confirmation'] && file_exists($output))
    {
      if (!$this->askConfirmation('Output file "'.$output.'" already exists. Overwrite ?'))
      {
        return false;
      }
    }
    
    // Execute script
    $this->executeCommand($command_bin, $command_args, $output);
    
    return true;
  }
  
  protected function executeCommand($bin, array $args = array(), $output_file = null)
  {
    $cmd = $bin;
    foreach ($args as $param => $value)
    {
      $cmd .= ' ' . $param . ' ' . escapeshellarg($value);
    }
    echo $cmd.PHP_EOL;
    
    $this->logSection('exec', $cmd);
    
    $descriptorspec = array(
       0 => array("pipe", "r"), // stdin
       1 => array("file", $output_file, "w"), // stdout
       2 => array("pipe", "w"), // stderr
    );
    $process = proc_open($cmd, $descriptorspec, $pipes);
    
    if (is_resource($process)) {
      // Start
      fclose($pipes[0]);
      // Read stderr
      $out = stream_get_contents($pipes[2]);
      fclose($pipes[2]);
      // Close process
      $return = proc_close($process);
    }
    
    if ($return != 0)
    {
      $this->logBlock($out, 'ERROR');
      throw new sfException('Command failed (returned ' . $return . ')');
    }
    
    $this->logBlock($out, 'INFO');
    $this->log('Written "'.$output_file.'"');
  }
  
}