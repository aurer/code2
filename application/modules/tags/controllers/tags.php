<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tags extends CI_Controller {

	public function __construct(){
        parent::__construct();
        $this->load->model('tags/tag_model');
		$this->load->model('clips/clip_model');
		$this->load->model('hit_model');
    }
    
    public function index()
	{
		$order = $this->input->get('order');
		if( !in_array($order, array('title','id','category','created','hits') ) ){
			$order = 'title';
		}
		$this->db->select('*, count(title) as count')->from('tags')->where('active', true)->group_by('title')->order_by("title");
		$viewdata['tags'] = querydata( $this->db->get() );
		$this->load->view('tags/tags-page-2', $viewdata);
				
		$this->template->render();
	}
	
	public function clips($tag=null){
		$this->load->library('pagination');
		$config['base_url'] = base_url() . 'clips/paged';
		$config['total_rows'] = $this->db->where('active',1)->count_all_results('clips');
		$config['per_page'] = 30;
		$config['first_tag_close'] = FALSE;
		$this->pagination->initialize($config);
		
		// Grab clips for this tag
		$viewdata['clips'] = $this->clip_model->clips_by_tag($tag);
		$viewdata['tags'] = $this->tag_model->tags_with_count();
		$viewdata['search'] = $tag;
		
		$sql = "SELECT id FROM tags WHERE lower( replace(title,' ', '-') )=? LIMIT 1";	
		$tagdata = querydata($this->db->query($sql, array(url_title($tag, 'dash', true))));
		$this->hit_model->hit('hits', $tagdata[0]['id']);
		
		$this->load->view('clips/clips-page', $viewdata);
		$this->template->render();
	}
	
	function manage(){
		$viewdata['tags'] = $this->tag_model->tags_with_count();
		$this->load->view('edit-tags-page', $viewdata);
		$this->template->render();
	}
	
	function update(){
		$old_name = $this->input->post('tag_name');
		$new_name = $this->input->post('new_name');		
		if($new_name != '' && $new_name != ''){
			$update = $this->tag_model->rename_tag($old_name, $new_name);
		}
		if($this->input->post('ajax') == 'true'){
			echo $update;
		} else {
			redirect('/tags/');
		}
	}
	
	function categorise(){
		$id = $this->input->post('id');
		$category = $this->input->post('category');
		if($id != '' && $category != ''){
			$update = $this->tag_model->categorise($id, $category);
		}
		//echo $update;
		if($this->input->post('ajax') == 'true'){
			echo $update;
		} else {
			redirect('/tags/');
		}
		
	}
	
	function delete(){
		$tag = $this->input->post('tag');
		if ($tag != '') {
			$deleted = $this->tag_model->delete('$tag');
		}
	}
	
	function get_clips($tag){
		// Lookup the tag is so we can 'hit' it		
		/*
		$sql = "SELECT id FROM tags WHERE lower( replace(title,' ', '-') )=? LIMIT 1";	
		$tagdata = querydata($this->db->query($sql, array(url_title($tag, 'dash', true))));
		die();
		
		$this->hit_model->hit('tags', $tagdata['id']);
		print_r($this->clip_model->clips_by_tag($tag));
		*/
		$sql = "SELECT id,title FROM clips WHERE active=1 AND id IN (SELECT clip_id FROM tags WHERE lower( replace(title,' ', '-') )=? )";	
		$tag = url_title($tag, 'dash', true);
		$result = querydata($this->db->query($sql, array($tag)));
		
		echo json_encode($result);
	}
	
	// Hmm nasty manually built JSON string due to needing unquoted keys
	// Used by the add/edit forms to suggest tags
	function autocomplete(){
		$query = $this->input->get('query');
		$data = "{ query : '$query', suggestions : [ '";
		$result = querydata($this->db->like('title', $query)->select('title')->get('tags_vw'));
		foreach ($result as $item) {
			$suggestions[] = $item['title']; 
		}
		$data .= implode("','", $suggestions);
		$data .= "' ] }";
		echo $data;
	}
	
}