<?php

class Welcome extends Base_Controller {

	function __construct()
	{
		parent::__construct();	
	}
	
	function index()
	{
		$this->template->set_message('My Message', 'success');
	
		$this->template->render();
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */