<?

class Feedback_model extends CI_model{
	
	function add($name, $email, $feedback){
		$data = array(
		   'name' => $name,
		   'email' => $email,
		   'feedback'=>$feedback,
		   'user_agent'=>$_SERVER['HTTP_USER_AGENT'],
		   'created'=>date('c')
		);
	    
		$this->db->insert('feedback', $data);
		$this->load->helper('email');
		$this->load->library('email');
		
		$message = "$name left some feedback on the ".$this->config->item('site_name')." site:\n\n";
		$message .= "$feedback\n\n";
		$message .= "$name\n$email";
		
		$this->email->from('feedback@code2.pro', $this->config->item('site_name').' feedback');
		$this->email->to($this->config->item('site_email'));		
		$this->email->subject($this->config->item('site_name').' feedback');
		$this->email->message($message);	
		
		$this->email->send();
	}
}