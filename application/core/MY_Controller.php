<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Base_Controller extends Controller {
	
	//---------------------------------------------------------------

	public function __construct()
	{
		parent::__construct();
				
		// Utilize the Bonfire Core classes
		$this->load->add_package_path(dirname(BASEPATH) .'/bonfire/third_party/bonfire/');
		
		/**
		 * Since we can't autoload resources from packages, we need
		 * to load our Bonfire Core classes here.
		 */
		$this->config->load('application');
		$this->load->library('Template');
		$this->load->library('Assets');
	}

	//---------------------------------------------------------------

	
}

// END Library class

/* End of file Library.php */
/* Location: ./application/libraries/Library.php */