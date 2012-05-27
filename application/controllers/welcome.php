<?php

class Welcome extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->helper('date');
		$this->load->model('Users','',TRUE);
	}


	function index()
	{
		$data = array(
			'members' => array()
		);
		if (!$this->Users->table_exists()){
			$this->load->view('no_table', $data);
		} else {
			$members = $this->Users->getAll(5); // Find all members, limit by 5
			if (count($members) != 0){
				foreach($members as $member){ // While we have results
					if ($member->id != ''){
						$data['members'][] = array(
							'id' => (string)$member->id->{AmazonDynamoDB::TYPE_NUMBER},
							'name' => (string)$member->name->{AmazonDynamoDB::TYPE_STRING},
							'address' => (string)$member->address->{AmazonDynamoDB::TYPE_STRING},
							'phone' => (string)$member->phone->{AmazonDynamoDB::TYPE_STRING},
							'date' => (string)$member->date->{AmazonDynamoDB::TYPE_NUMBER}
						);
					}
				}
			}
			$this->load->view('welcome', $data);
		}
	}
	
	function add()
	{
		$data = array();
		$data['inserted'] = FALSE;
		
		// If form submitted
		if($this->input->post('add'))
		{
			// add new member into array
			$member = array(
				'name' => $this->input->post('name'),
				'address' => $this->input->post('address'),
				'phone' => $this->input->post('phone'),
				'date' => time()
			);
			$this->Users->save($member); // Insert the member
			
			$data['inserted'] = TRUE;
		}
		
		$this->load->view('add', $data); // Load the form
	}
	
	function edit($memberid=0)
	{
		$member = $this->Users->getByID($memberid); // Find member details
		$data = array(
			'id' => (string)$member->id->{AmazonDynamoDB::TYPE_NUMBER},
			'name' => (string)$member->name->{AmazonDynamoDB::TYPE_STRING},
			'address' => (string)$member->address->{AmazonDynamoDB::TYPE_STRING},
			'phone' => (string)$member->phone->{AmazonDynamoDB::TYPE_STRING},
			'date' => (string)$member->date->{AmazonDynamoDB::TYPE_NUMBER},
			'inserted' => FALSE
		);
		
		// If form submitted
		if($this->input->post('edit'))
		{
			// add new member into array
			$member = array(
				'id' => (string)$memberid,
				'name' => $this->input->post('name'),
				'address' => $this->input->post('address'),
				'phone' => $this->input->post('phone'),
				'date' => $data['date']
			);
			$this->Users->save($member); // Insert the member
			
			$data['inserted'] = TRUE;
			redirect('/', 'refresh');
		}
		
		$this->load->view('edit', $data); // Load the form
	}
	
	function view($memberid)
	{
		$members = $this->Users->getByID($memberid); // Find member details
		
		$data = array(
			'id' => $members['id'],
			'name' => $members['name'],
			'address' => $members['address'],
			'phone' => $members['phone'],
			'date' => $members['date']
		);
		
		$this->load->view('view', $data);
	}
	
	function delete($memberid)
	{
		$members = $this->Users->deleteById($memberid); // Find member details
		redirect('/', 'refresh');
	}

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */