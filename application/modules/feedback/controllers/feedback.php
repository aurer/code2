<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Feedback extends CI_Controller {

	public function index()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('feedback_model');
		
		$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
		
		$this->form_validation->set_rules('name', null, 'trim|required');
		$this->form_validation->set_rules('email', null, 'trim|required|valid_email');
		$this->form_validation->set_rules('feedback', null, 'required');
		
		if ($this->form_validation->run() == false){
			$this->load->view('feedback/feedback-form');
			$this->template->render();
		} else {
			$this->feedback_model->add(
				$this->input->post('name'),
				$this->input->post('email'),
				$this->input->post('feedback')
			);
			redirect('/feedback/thanks/');
		}
	}
	public function thanks(){
		$this->load->view('feedback/feedback-thanks');
		$this->template->render();
	}
}