<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	Library: Template
	
	The Template library enforces very strict view organization to
	ease development time, while keeping performance to a maximum.
	
	All views are assumed to be located under views/controller/method.php.
	All views are rendered within a layout, which must be found in
	/views/layouts/layout_name.php.
*/

class Template {
	
	// Variable: ci
	// An instance of the CodeIgniter object
	private $ci;
	
	// Variable: layout
	// The name of the layout file to be used.
	private $layout		= 'application';
	
	// Variable: view
	// The name of the view to be rendered.
	private $view		= '';
	
	// Variable: data
	// key/value pairs to be sent to the views.
	private $data		= array();
	
	// Variable: message
	// Stores a message to be sent. Similar to set_flashdata, but works on current request also.
	private $message	= '';

	//---------------------------------------------------------------

	public function __construct()
	{
		// Get our CodeIgniter Instance
		$this->ci =& get_instance();
	}

	//---------------------------------------------------------------
	
	public function render($layout=null) 
	{
		if (!empty($layout) && is_string($layout))
		{
			$this->layout = $layout;
		}
		
		// Is it in an AJAX call? If so, override the layout
		if ($this->is_ajax())
		{
			$layout = $this->ci->config->item('OCU_ajax_layout');
			$this->ci->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
			$this->ci->output->set_header("Cache-Control: post-check=0, pre-check=0");
			$this->ci->output->set_header("Pragma: no-cache"); 
		}
		
		// Render the layout - Ocular will provide the 404 if needed.
		$this->ci->load->view('layouts/'. $this->layout .'.php');
	}
	
	//---------------------------------------------------------------
	
	/*
		Method: yield
		
		Renders the current view. By default, this view is assumed to 
		match the current controller/method.
		
		Also responsible for making sure that the data is available to the views.
	*/
	public function yield() 
	{
		// We need the current controller/function to determine view name,
		// but only if the 
		if (empty($this->view))
		{
			$this->view = $this->ci->router->class . '/' . $this->ci->router->method;
		}
		
		// Make data available to the views
		if (count($this->data))
		{
			$this->ci->load->vars($this->data);
		}
		
		$this->ci->load->view($this->view);
	}
	
	//---------------------------------------------------------------
	
	//---------------------------------------------------------------
	// !UTILITY FUNCTIONS
	//---------------------------------------------------------------
	
	/*
		Method: set_layout
		
		Use this method to override the default layout of 'application'.
		
		Parameter: layout - the name of the layout to use
	*/
	public function set_layout($layout=null) 
	{
		if (empty($layout) || !is_string($layout))
		{
			return;
		}
		
		$this->layout = $layout;
	}
	
	//---------------------------------------------------------------
	
	/*
		Method: set_view
		
		Use this method to overide the default view.
		
		Parameter: view - the name of the view to set
	*/
	public function set_view($view=null) 
	{
		if (empty($view) || !is_string($view))
		{
			return;
		}
		
		$this->view = $view;
	}
	
	//---------------------------------------------------------------
	
	/*
		Method: is_ajax
		
		Returns: TRUE if the call is the result of an ajax call.
	*/
	public function is_ajax() 
	{
		return ($this->ci->input->server('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest') ? TRUE : FALSE;
	}

	//---------------------------------------------------------------
	
	/*
		Method: set
		
		Sets data to be made available to the views.
		
		Parameters: 
			var		- can be either a string or an array or key/value pairs to be set
			value	- the value of var, if var is a string
	*/
	public function set($var=null, $value=null) 
	{
		if (is_string($var))
		{
			$this->data[$var] = $value;
		} else if (is_array($var))
		{
			$this->data = array_merge($this->data, $var);
		}
	}
	
	//---------------------------------------------------------------
	
	/*
		Method: set_message
		
		Sets a flash message that can be displayed both in the current
		session, and upon page refresh.
		
		Parameters: 
			message - A string that is the message to be stored.
			type	- A string that is the class to added to the message template.
	*/
	public function set_message($message='', $type='info') 
	{
		if (empty($message) || !is_string($message))
		{
			return;
		}
	
		if (class_exists('CI_Session'))
		{
			$this->ci->session->set_flashdata('message', $type.'::'.$message);
		}
		
		$this->message = array('type'=>$type, 'message'=>$message);
	}
	
	//---------------------------------------------------------------
	
	/*
		Method: message
		
		Displays a status message (small success/error messages).
		If data exists in 'message' session flashdata, that will 
		override any other messages. The renders the message based
		on the template provided in the config file ('template.message_template').
		
		Returns: string with the formatted message.
	*/
	public function message() 
	{
		$message = $type = '';
	
		// Does session data exist? 
		if (class_exists('CI_Session'))
		{
			$message = $this->ci->session->flashdata('message');
		}

		if (!empty($message))
		{
			// Split out our message parts
			list($type, $message) = explode('::', $message);
		} else 
		{
			// Grab the data from our own store
			if (count($this->message))
			{
				$message 	= $this->message['message'];
				$type		= $this->message['type'];
			}
		}
		
		// Grab out message template and replace the placeholders
		$template = str_replace('{type}', $type, $this->ci->config->item('template.message_template'));
		$template = str_replace('{message}', $message, $template);
				
		return $template;
	}
	
	//---------------------------------------------------------------
}

// END Library class

/* End of file Library.php */
/* Location: ./application/libraries/Library.php */