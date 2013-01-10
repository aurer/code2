<?

class User_model extends CI_Model{
	
	function add($username, $email, $password){
		$data = array(
		   'username' => $username,
		   'email' => $email,
		   'password'=>sha1($password)
		);
	    ;
		$this->db->insert('users', $data);
		return $this->db->insert_id();
	}
}