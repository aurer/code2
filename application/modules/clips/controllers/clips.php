<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Clips extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('clip_model');
        $this->load->model('tags/tag_model');
        $this->load->model('hit_model');
        //redirect('clips', 'refresh');
        //$this->index();
	    //$this->output->_display();
	    //exit();
    }
    
	public function index()
	{	
		// Auto redirect to /clips/ to help with nav states
		if( uri_string() == '' ){
            redirect('/clips/');   
        }
		$this->paged();
	}
	
	public function paged($page=1, $tag=false){
				
		$this->load->library('pagination');
		$config['base_url'] = base_url() . 'clips/paged';
		$config['total_rows'] = $this->db->where('active',1)->count_all_results('clips');
		$config['per_page'] = 30;
		$config['first_tag_close'] = FALSE;
		$this->pagination->initialize($config);
		
		$viewdata['clips'] = $this->clip_model->get_clips($config['per_page'], $page);
		$viewdata['paged'] = true;
		
		$this->load->view('clips-page', $viewdata);
		$this->template->render();
	}
			
	function search(){
		$query = $this->input->get('q');
		$this->db->like('title', $query)->or_like('description', $query);
		$result = $this->db->get('clips_vw');

		$pagedata['clips'] = querydata($result);
		
		$this->load->view('search-page', $pagedata);
		$this->template->render();
	}
	
	/*
	public function by_tag($tag){
		echo json_encode($this->clip_model->clips_by_tag($tag));
	}
	*/
	
	/*
	public function tags($tag=''){
		$viewdata['clips'] = $this->clip_model->clips_by_tag($tag);
		$viewdata['tags'] = $this->tag_model->get_distinct();
		$viewdata['search'] = $tag;
		$this->load->view('clips/clips-page', $viewdata);
		$this->template->render();
	}*/
	
	public function view($id=null){
		$this->hit_model->hit('clips',$id);
		$clips = $this->clip_model->get_clip($id);		
		if(is_array($clips) > 0){
			$viewdata['clip'] = $clips[0];
		}
		$viewdata['tags'] = $this->tag_model->tags_for_clip($id);
		$this->load->view('view-clip', $viewdata);
		$this->template->render();
	}

	public function raw($id=null){
		$clips = $this->clip_model->get_clip($id);		
		if(is_array($clips) > 0){
			$viewdata['clip'] = $clips[0];
		}
		$viewdata['tags'] = $this->tag_model->tags_for_clip($id);
		$this->load->view('raw-clip', $viewdata);
		$this->template->render();
	}
	
	public function add(){
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
		
		$this->form_validation->set_rules('title', 'Title', 'required');
		$this->form_validation->set_rules('tags', 'Tags', 'required');
		$this->form_validation->set_rules('code', 'Code', 'required');
		$this->form_validation->set_rules('private', 'Private');
		$this->form_validation->set_rules('description', 'Description');
		
		if ($this->form_validation->run() == false){
			$this->load->view('add-clip');
			$this->template->render();
		} else {
			$this->clip_model->add(
				$this->input->post('title'),
				$this->input->post('tags'),
				$this->input->post('code'),
				$this->input->post('description'),
				($this->input->post('private')=='true')
			);
			if($this->input->post('nextpage')){
				redirect($this->input->post('nextpage'));
			} 
			else {
				redirect('/');
			}
		}
	}
	
	public function edit($id=null){
		$this->load->helper('form');
		$this->load->library('form_validation');
		
		$clips = $this->clip_model->get_clip($id);		
		if(is_array($clips) > 0){
			$viewdata['clip'] = $clips[0];
		}
		if(count($clips) < 1){
			show_404();
		}
		$viewdata['tags'] = array();
		$tags = $this->tag_model->tags_for_clip($id);
		foreach($tags as $tag){
			array_push($viewdata['tags'], $tag['title']);
		}
		
		// Form validation
		$this->form_validation->set_error_delimiters('<div class="error">', '</div>');	
		$this->form_validation->set_rules('title', 'Title', 'required');
		$this->form_validation->set_rules('tags', 'Tags', 'required');
		$this->form_validation->set_rules('code', 'Code', 'required');
		$this->form_validation->set_rules('private', 'Private');
		$this->form_validation->set_rules('description', 'Description');
		
		if ($this->form_validation->run() == FALSE){
			$this->load->view('edit-clip', $viewdata);
			$this->template->render();
		} else {
			$this->clip_model->update(
				$id,
				$this->input->post('title'),
				$this->input->post('tags'),
				$this->input->post('code'),
				$this->input->post('description'),
				($this->input->post('private')=='true')
			);
			if($this->input->post('nextpage')){
				redirect($this->input->post('nextpage'));
			} 
			else {
				redirect("/clips/view/$id/");
			}
		}
	}
	
	public function delete($id=null){
		$result = $this->clip_model->delete($id);
		if($this->input->get('ajax') == 'true'){
			echo $result;
		} else {
			redirect('/');
		}
	}

	public function restore($id){
		$result = $this->clip_model->restore($id);
		if($this->input->get('ajax') == 'true'){
			echo $result;
		} else {
			redirect('/');
		}
	}
	
	public function json($start=0,$limit=10){	
		$this->db->select('title,id,code,description');
		$this->db->limit($limit, $start);
		$result = querydata($this->db->get('clips'));
		echo json_encode($result);
	}
	
}