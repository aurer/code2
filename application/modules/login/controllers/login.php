<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {

	public function index()
	{	
		$this->load->view('index');
		
		if( $this->input->post('username') != '' && $this->do_login() === true ){
			redirect('/');
		}
		else{
			$this->template->render();
		}
	}
	
	public function do_login(){
		
		$username = $this->input->post('username');
		$password = sha1($this->input->post('password'));
	
		$result = $this->db->get_where('users', array("username"=>$username, "password"=>$password), 1);
		
		if( is_array($result->result_array()) && count($result->result_array()) > 0){
			$data = $result->result_array();
			
			$this->session->set_userdata('logged_in',	 	true);
			$this->session->set_userdata('user_id', 		$data[0]['id']);
			$this->session->set_userdata('user_username', 	$data[0]['username']);
			$this->session->set_userdata('user_email', 		$data[0]['email']);
			$this->session->set_userdata('user_joined', 	$data[0]['created']);
			
			return true;
		}
		
		return false;
	}
	
	public function logout(){
		$this->session->sess_destroy();
		redirect('/');
	}
}