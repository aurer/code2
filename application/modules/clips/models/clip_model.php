<?

class Clip_model extends CI_model{

	function __construct(){
		parent::__construct();
	}
	
	function get_clips($num=false,$offset=1){
		
		$offset = $num*($offset-1);
		
		$this->db->where('active', 1);
		$this->db->order_by('id DESC');
		$result = $this->db->get('clips', $num, $offset);
		return querydata($result);
	}
	
	function get_clip($id){
		$this->db->where('id', $id)->where('active', 1);
		return querydata($this->db->get('clips'));
	}
	
	function latest_clips(){
		$this->db->limit(5)->where('active', 1);
		$this->db->order_by('created DESC');
		return querydata($this->db->get('clips'));
	}
		
	function add($title, $tags, $code, $description, $private){
		$data = array(
		   'title' => $title,
		   'code' => $code,
		   'description'=>$description,
		   'private'=>$private,
		   'created'=>date('c')
		);
        
        $this->db->trans_start();
		$this->db->insert('clips', $data);
		$insertid = $this->db->insert_id();
		foreach(explode(",", $tags) as $tag){
			
			// Lookup any existsting category for this tag
			$getTagCat = $this->db->query("SELECT category FROM tags WHERE category IS NOT NULL AND title = '$tag' LIMIT 1");
			$tagCategory = $getTagCat->result_array();
			
			$this->db->insert('tags', array(
				'title'=>trim($tag),
				'clip_id'=>$insertid,
				'created'=>date('c'),
				'category'=>$tagCategory[0]['category']
			));
		}
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE){
            return false;
        } else {
            return true;
        }
	}
	
	function update($id, $title, $tags, $code, $description, $private){
		$data = array(
		   'title' => $title,
		   'code' => $code,
		   'description' => $description,
		   'private'=>$private
		);
		
		$this->db->trans_start();
		// Update clip
		$this->db->where('id', $id);
		$this->db->update('clips', $data);
		
		// Delete all tags for this clip
		$this->db->where('clip_id', $id);
		$this->db->delete('tags');
		
		// Add new tags for this clip
		foreach(explode(",", $tags) as $tag){
			$this->db->insert('tags', array(
				'title'=>trim($tag),
				'clip_id'=>$id
			));
		}
		$this->db->trans_complete();
        if ($this->db->trans_status() === FALSE){
            return false;
        } else {
            return true;
        }
	}
	
	function delete($id){
		$this->db->trans_start();
		// Delete the clip
		$this->db->where('id', $id);
		$this->db->update('clips', array('active'=>0));
		
		$result = $this->db->affected_rows(); 
		
		// Delete tags for this clip
		$this->db->where('clip_id', $id);
		$this->db->update('tags', array('active'=>0));
		
		$this->db->trans_complete();
        if ($this->db->trans_status() === FALSE){
            return false;
        } else {
            return $result;
        }
	}

	function restore($id){
		$this->db->trans_start();
		// Restore the clip
		$this->db->where('id', $id);
		$this->db->update('clips', array('active'=>1));
		
		$result = $this->db->affected_rows(); 
		
		// Delete tags for this clip
		$this->db->where('clip_id', $id);
		$this->db->update('tags', array('active'=>1));
		
		$this->db->trans_complete();
        if ($this->db->trans_status() === FALSE){
            return false;
        } else {
            return $result;
        }
	}
	
	function clips_by_tag($tag){
		$sql = "SELECT * FROM clips WHERE active=1 AND id IN (SELECT clip_id FROM tags WHERE lower( replace(title,' ', '-') )=? )";	
		return querydata($this->db->query($sql, array(url_title($tag, 'dash', true))));
	}
	
	function popular_clips(){
		$this->db->select('*')->from('clips_vw')->where('hits >', 0)->order_by('hits DESC')->limit(5);
		return querydata( $this->db->get() );
	}
	
}