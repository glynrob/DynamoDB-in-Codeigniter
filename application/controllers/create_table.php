<?php

class Create_table extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->helper('date');
		$this->load->model('Users','',TRUE);
	}


	function index()
	{
		if ($this->Users->create_table() == true){
			redirect('/', 'refresh');
		}
	}

}

/* End of file create_table.php */
/* Location: ./application/controllers/create_table.php */