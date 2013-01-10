<?php

/*
* Class: Template
* Description: Super simple template class for Codeigniter.
* Version: 2.0
* Author: Phil Maurer
* Author URI: http://aurer.co.uk/
*/

/*
    FUNCTIONS
    
    load($view);
    use_template($template);
    set($item, $value);
	get($item [,$before] [,$after]);
	open_section($section);
	close_section();
	get_section($section [,$before] [,$after]);
	render();
*/


if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Template {
	
	public $content=array();
    public $storage=array();
	public $template_dir;
    public $template;
	
	private $_current_section;
	private $_CI;
	
	public function __construct()
	{
		$this->_CI =& get_instance();
		$this->template_dir = APPPATH."templates";
        $this->template = false;
	}

    // Load the view page, then render it
    public function load($view, $output=true)
    {
        $this->_CI->load->view($view, '', true);
        if($output) $this->render();
    }
	
    // Set a stored variable
    public function set($item, $value)
    {   
        $this->storage[$item] = $value;
    }

    // Retrieve a stored variable
    public function get($item, $before='', $after='')
    {   
        if( !empty($this->storage[$item]) ){
            echo $before;
            echo htmlentities($this->storage[$item]);
            echo $after;
        }
    }

    // Define the start of a section, intitialise the output buffer
    public function open_section($section){
        $this->_current_section = $section;
        ob_start();
    }
    
    // Set the content for a section from the output buffer
    public function close_section(){
    	$this->content[$this->_current_section] = ob_get_clean();
    }

    // Get the content of a section - use in the template file
    public function get_section($section, $before='', $after=''){
        if( !empty($this->content[$section]) ){
            echo $before;
            echo $this->content[$section];
            echo $after;
        }
    }
    
    // Set the template file to use
    public function use_template($name)
    {
    	ob_clean(); // Remove any content already in the buffer
        $this->template = FCPATH.$this->template_dir.'/'.$name.EXT;
    }
    
    // Render view page
    public function render()
    {
        // No template specified so output the view page directly
        if( empty($this->template) ){
    		$output = implode('', $this->content);
            $this->_CI->output->set_output($output);
    		return;
    	}
    	
        // Template was specified, if it exists render it otherwise display an error
        if(is_file($this->template)){
            ob_start();
    		include $this->template;
            $output = ob_get_clean();
            $this->_CI->output->set_output($output);
    	} else {
    		show_error("The template file '$this->template' could not be found", 404);
    	}
    }
}