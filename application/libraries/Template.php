<?php

/*
* Class: Template
* Description: Super simple template class for Codeigniter.
* Version: 1.0
* Author: Phil Maurer
* Author URI: http://aurer.co.uk/
*/

/*
    SUGGESTED NEW FUNCTIONS
    
    load($view);
    use_template($template);
    set($item, $value);
	get($item, $before='', $after='');
	open_section($section);
	close_section();
	get_section($section);
	render();
*/


if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Template {
	
	public $parts=array();
	public $template_dir;
    public $template;
	public $views_dir;
	
	private $_set;
	private $_CI;
	
	public function __construct()
	{
		$this->_CI =& get_instance();
		$this->template_dir = APPPATH."templates";
        $this->template = false;
		$this->views_dir = FCPATH."application/views/";
	}

    public function load($view)
    {
        $this->_CI->load->view($view, '', true);
    }
	
    public function set($part, $value=false)
    {   
        if($value!==false){
            $this->parts[$part] = $value;
        } else {
    	   $this->_set = $part;
    	   ob_start();
        }
    }
    
    public function end()
    {
    	$this->parts[$this->_set] = ob_get_clean();
    }
    
    public function use_template($name)
    {
    	ob_clean(); // Remove any content already in the buffer
        $this->template = FCPATH.$this->template_dir.'/'.$name.EXT;
    }

    public function end_template()
    {
        $this->render();
    }
    
    public function render()
    {
        if($this->template === false){
    		echo implode('', $this->parts);
    		return;
    	}

        extract($this->parts);
    	
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