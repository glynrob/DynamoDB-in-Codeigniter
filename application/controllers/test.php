<?php

class Test extends CI_Controller {


	function index()
	{
		include_once($this->config->item('dynamodb').'sdk.class.php');
		include (APPPATH.'/config/aws.inc.php');
		include($this->config->item('dynamodb').'_compatibility_test/sdk_compatibility_test.php'); // compatibility test if you require
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */