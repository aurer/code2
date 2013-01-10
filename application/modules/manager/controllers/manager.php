<?php

class Manager extends CI_Controller{
	
	public $basepath = "/manager";

	public function __construct(){
		parent::__construct();
		$this->load->library('DBmanager');
		$this->load->library('template');
		$this->load->helper('form');
	}
	
	public function index()
	{
		$this->template->load('manager');
		$this->template->render();
	}
	
	public function reload_config($force=false){
		$this->dbmanager->reload_config($force);
		redirect($this->basepath);
	}
	
	public function reload_table($tablename, $redirect=true){
		if( $this->dbmanager->is_section($tablename) ){
			$this->dbmanager->update_section($tablename);
		}
		if($redirect!=='false'){
			redirect($this->basepath);
		} else {
			echo $this->dbmanager->check_section($tablename)? 'Ok' : 'Mismatch';
		}
	}
	
	public function reload_view($viewname, $redirect=true){
		if( $this->dbmanager->is_view($viewname) ){
			$created = $this->dbmanager->update_view($viewname);
		}
		if($redirect!=='false'){
			redirect($this->basepath);
		} else {
			if($created){
				echo $this->dbmanager->check_view($viewname)? 'Ok' : 'Mismatch';
			} else {
				echo "Error";
			}
		}
	}
}