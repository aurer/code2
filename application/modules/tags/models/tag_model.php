<?

class Tag_model extends CI_model{

	function __construct(){
		parent::__construct();
	}
	
	function get_distinct(){
		$this->db->select('title')->distinct();
		$this->db->where('active', 1);
		$query = $this->db->get('tags');
		return querydata($query);
	}
	
	function tags_for_clip($id){
		$this->db->where('clip_id', $id)->where('active', 1);
		return querydata($this->db->get('tags'));
	}
	
	function tags_with_count(){
		$this->db->select('id, title, category, count(title) as count')->group_by('title')->where('active', 1);
		return querydata($this->db->get('tags'));
	}
	
	function rename_tag($old_name, $new_name){
		$this->db->where('title', $old_name);
		$this->db->update('tags', array('title'=>$new_name));
		return $this->db->affected_rows();
	}
	
	function tag($id){
		$this->db->where('id', $id)->limit(1)->where('active', 1);
		$query = $this->db->get('tags');
		return querydata($query);
	}
	
	function popular_tags(){
		$this->db->select('*')->from('tags_vw')->where('hits >', 0)->where('active', 1)->order_by('hits DESC')->limit(5);
		return querydata( $this->db->get() );
	}
	
	function delete($tag) {
		$this->db->where('title', $tag);
		$this->db->delete('tags');
	}
	
	function categorise($tag, $type){
		$this->db->where('title', $tag);
		$this->db->update('tags', array('category'=>$type) );
		return $this->db->last_query(); //$this->db->affected_rows();
	}
	
	function by_popularity(){
		$this->db->select('*')->from('tags_vw')->order_by('hits DESC');
		return querydata( $this->db->get() );
	}
}