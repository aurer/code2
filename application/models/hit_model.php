<?

class Hit_model extends CI_model{

	function __construct(){
		parent::__construct();
	}
		
	function hit($section, $id){
		
		$lookup = $this->db->get_where('hits', array('section'=>$section, 'item_id'=>$id) );
		
		if($lookup->num_rows() > 0){
			
			$hits = querydata($lookup);
			
			$data = array(
				'hits'=>$hits[0]['hits']+1
			);
			$this->db->where('item_id',$id);
			$this->db->update('hits', $data);
		} else {
			$data = array(
				'item_id'=>$id,
				'section'=>$section,
				'hits'=>1,
				'created'=>date('c')
			);
			$this->db->insert('hits', $data);
		}        
	}
}