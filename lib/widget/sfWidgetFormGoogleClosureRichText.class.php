<?php

class sfWidgetFormGoogleClosureRichText extends sfWidgetFormTextarea
{

  protected function getDefaultEditorPlugins()
  {
    return array(
      'BasicTextFormatter' => array(),// basic formatting options
      'RemoveFormatting' => array(),  // allow option to unapply all formatting
      'UndoRedo' => array(),          // Allow undo/redo options
      'ListTabHandler' => array(),    // Handle "tab" to increment a list
      'SpacesTabHandler' => array(),  // 
      'TagOnEnterHandler' => array('P'),      // Handle "enter" to generate a tag
      'HeaderFormatter' => array(),   // Support heading styles
    );
  }
  
  protected function getDefaultEditorButtons()
  {
    return array(
      'BOLD',
      'ITALIC',
      'UNDERLINE',
      'FONT_COLOR',
      'BACKGROUND_COLOR',
      'FONT_FACE',
      'FONT_SIZE',
      'UNDO',
      'REDO',
      'UNORDERED_LIST',
      'ORDERED_LIST',
      'INDENT',
      'OUTDENT',
      'JUSTIFY_LEFT',
      'JUSTIFY_CENTER',
      'JUSTIFY_RIGHT',
      'SUBSCRIPT',
      'SUPERSCRIPT',
      'STRIKE_THROUGH',
      'REMOVE_FORMAT',
    );
  }
  
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    
    $id_suffix = uniqid('');
    
    $this->addOption('plugins', array());
    $this->addOption('buttons', $this->getDefaultEditorButtons());
    
    $this->addOption('editor.Command', 'goog.editor.Command');
    $this->addOption('editor.Field', 'goog.editor.Field');
    $this->addOption('editor.Toolbar', 'goog.ui.editor.DefaultToolbar');
    $this->addOption('editor.ToolbarController', 'goog.ui.editor.ToolbarController');
  }
  
  public function getGoogLibraries()
  {
    $libraries = array(
      'goog.dom',
      $this->getOption('editor.Command'),
      $this->getOption('editor.Field'),
      $this->getOption('editor.Toolbar'),
      $this->getOption('editor.ToolbarController'),
    );
    foreach ($this->getEditorPlugins() as $plugin => $arguments)
    {
      $libraries[] = $this->getEditorPluginClass($plugin);
    }
    
    return $libraries;
  }
  
  public function getJavascripts()
  {
    return array(GoogleClosureUtils::getGoogBaseJavascript());
  }
  
  public function getStylesheets()
  {
    return array(
      GoogleClosureUtils::getGoogStylesheet('button')                   => 'screen',
      GoogleClosureUtils::getGoogStylesheet('menus')                    => 'screen',
      GoogleClosureUtils::getGoogStylesheet('palette')                  => 'screen',
      GoogleClosureUtils::getGoogStylesheet('toolbar')                  => 'screen',
      GoogleClosureUtils::getGoogStylesheet('editortoolbar', 'editor')  => 'screen',
    );
  }
  
  protected function getEditorPlugins()
  {
    return array_merge($this->getDefaultEditorPlugins(), $this->getOption('plugins'));
  }
  
  protected function getEditorButtons()
  {
    return $this->getOption('buttons');
  }
  
  protected function getEditorPluginClass($plugin)
  {
    if (false === strpos($plugin, '.'))
    {
      $plugin = 'goog.editor.plugins.' . $plugin;
    }
    
    return $plugin;
  }
  
  protected function renderEditorPluginDeclaration($plugin, array $arguments)
  {
    $plugin = $this->getEditorPluginClass($plugin);
    
    $js_args = array();
    foreach ($arguments as $argument)
    {
      $js_args[] = json_encode($argument);
    }
    
    return 'new '.$plugin.'('.implode(',', $js_args).')';
  }
  
  protected function getEditorButtonFullName($button)
  {
    return $this->getOption('editor.Command') . '.' . $button;
  }
  
  protected function renderEditorButtonsDeclaration()
  {
    $buttons = array();
    foreach ($this->getEditorButtons() as $button)
    {
      $buttons[] = $this->getEditorButtonFullName($button);
    }
    
    return '[' . implode(','."\n", $buttons) . ']';
  }
  
  protected function renderEditorJavascript($attributes = array(), $anonymous = true)
  {
    $id = $attributes['id'];
    
    $edit_attributes = $this->getEditFieldAttributes($attributes);
    $edit_id = $edit_attributes['id'];
    
    $toolbar_attributes = $this->getToolbarAttributes($attributes);
    $toolbar_id = $toolbar_attributes['id'];
    
    $js = "var f=new {$this->getOption('editor.Field')}('$edit_id');\n";
    // Plugins
    foreach ($this->getEditorPlugins() as $plugin => $arguments)
    {
      $js .= 'f.registerPlugin('.$this->renderEditorPluginDeclaration($plugin, $arguments).');'."\n";
    }
    // Toolbar
    $buttons = $this->renderEditorButtonsDeclaration();
    $js .= "var t={$this->getOption('editor.Toolbar')}.makeToolbar($buttons,goog.dom.$('$toolbar_id'));\n";
    $js .= "var c=new {$this->getOption('editor.ToolbarController')}(f,t);\n";
    // Attach events
    $js .= "var u=function(){goog.dom.$('$id').value=f.getCleanContents();};\n";
    $js .= "goog.events.listen(f,{$this->getOption('editor.Field')}.EventType.DELAYEDCHANGE,u);\n";
    // Start editor
    $js .= "f.makeEditable();\n";
    $js .= "u();\n";
    
    if ($anonymous)
    {
      $js = '(function(){'.$js.'})();';
    }
    
    return $js;
  }
  
  public function renderGoogRequires()
  {
    return GoogleClosureUtils::getGoogRequire($this->getGoogLibraries(), '');
  }
  
  protected function getToolbarAttributes($attributes)
  {
    return array(
      'id' => 'toolbar_'.$attributes['id'],
      'class' => 'goog-rich-editor-toolbar',
    );
  }
  
  protected function getEditFieldAttributes($attributes)
  {
    return array(
      'id' => 'edit_'.$attributes['id'],
      'class' => 'goog-rich-editor-edit',
      'style' => 'width:'.$attributes['cols'].'em;height:'.(2*$attributes['rows']).'em;',
    );
  }
  
  protected function renderWrapped($html, $attributes)
  {
    return $this->renderContentTag('div', $html, array(
      'id' => 'wrap_'.$attributes['id'],
      'class' => 'goog-rich-editor',
      'style' => 'width:'.$attributes['cols'].'em;',
    ));
  }
  
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $attributes['name'] = $name;
    $attributes = $this->fixFormId($attributes);
    
    unset($this->attributes['cols'], $this->attributes['rows']);
    
    $html = '';
    
    $html .= $this->renderContentTag('div', '', $this->getToolbarAttributes($attributes));
    $html .= $this->renderContentTag('div', '', $this->getEditFieldAttributes($attributes));
    
    $html .= $this->renderTag('input', array(
      'type' => 'hidden', 
      'name' => $name, 
      'value' => $value, 
      'id' => $attributes['id']));
    
    $html .= $this->renderContentTag('script', $this->renderGoogRequires(), array('type' => 'text/javascript'));
    $html .= $this->renderContentTag('script', $this->renderEditorJavascript($attributes), array('type' => 'text/javascript'));
    
    return $this->renderWrapped($html, $attributes);
  }
  
}
