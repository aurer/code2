<? 

class Users extends CI_Controller{
	
	public function __construct(){
	    parent::__construct();
	}
	
	function index(){
		redirect('/users/register');
	}
	
	function register(){		
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('user_model');
		$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
		
		$this->form_validation->set_rules('username', 'Username', 'required|is_unique[users.username]');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'required|min_length[5]');
		$this->form_validation->set_rules('password__confirm', 'Confirm password', 'required|min_length[5]|matches[password]');
		
		if ($this->form_validation->run() == false){
			$this->template->load('register');
		} else {
			$user_id = $this->user_model->add(
				$this->input->post('username'),
				$this->input->post('email'),
				$this->input->post('password')
			);
			$this->session->set_userdata('new_user_id', $user_id);
			if($this->input->post('nextpage')){
				redirect($this->input->post('nextpage'));
			} 
			else {
				redirect('/users/welcome');
			}
		}
	}
	
	function welcome(){
		$id = $this->session->userdata('new_user_id');
				
		$result = $this->db->get_where('users', array('id'=>$id), 1);
		
		$result = $result->result_array();
		
		$viewdata['user'] = $result[0];
		
		if(!empty($id)) $this->load->view('welcome', $viewdata);
		else redirect('/users');
		
		$this->session->unset_userdata('new_user_id');
	}
}