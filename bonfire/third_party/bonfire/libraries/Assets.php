<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	Class: Assets
	
	Handles organizing css, js, and image files.
*/
class Assets {

	// Variable: ci
	// An instance of the CI superobject
	private $ci;
	
	// Variable: asset_folder
	// The folder (under the views folder) that stories the assets to use
	private $asset_folder		= 'assets/';

	// Variable: inline_scripts
	// Storage for the scripts that will appear on the page itself.
	private $inline_scripts		= array();
	
	// Variable: external_scripts
	// Storage for the scripts that will be linked to.
	private $external_scripts 	= array();
	
	// Variable: styles
	// The CSS files to be included.
	private $styles				= array();	
	
	// Variable: packages
	// The packages of assets available.
	private $packages			= array();

	//---------------------------------------------------------------

	public function __construct() 
	{
		$this->ci =& get_instance();
		
		//$this->ci->benchmark->mark('constructor_start');
	
		// Grab our packages
		if ($p =& $this->ci->config->item('assets.packages'))
		{
			$this->packages = $p;
		}
	
		// Load the file helper, since we'll be using it a lot
		if (!function_exists('write_file'))
		{
			$this->ci->load->helper('file');
		}
		
		//$this->ci->benchmark->mark('constructor_end');
	}
	
	//---------------------------------------------------------------
	
	//---------------------------------------------------------------
	// !STYLESHEET FUNCTIONS
	//---------------------------------------------------------------
	
	/*
		Method: add_css 
		
		Accepts either an array or a string with a single css file name
		and appends them to the base styles in $this->styles;
		
		The file names should NOT have an extension added on to them.
		
		Parameter: styles - either a string or array of file names.
	*/
	public function add_css($styles=null) 
	{
		//$this->ci->benchmark->mark('add_css_start');
	
		if (empty($styles))
		{
			return;
		}
	
		// Handle String values
		if (is_string($styles))
		{
			$this->styles[] = $styles;
		} 
		
		// Process arrays
		else if (is_array($styles) && count($styles) != 0)
		{
			foreach ($styles as $style)
			{
				$this->styles[] = $style;
			}
		} 
		
		//$this->ci->benchmark->mark('add_css_end');
	}
	
	//---------------------------------------------------------------
	
	/*
	 	Method: css
	 	
	 	Creates the proper links for inserting into the HTML head, 
	 	depending on whether devmode is 'dev' or other (test/production).
	 	
	 	The file names should NOT have the extension.
	 	
	 	Parameter: styles - either a string or array of package/file names
	*/
	public function css($styles=null, $override=false) 
	{
		//$this->ci->benchmark->mark('css_start');
	
		// If neither the user nor our stores has any files, get out of here.
		if (empty($styles) && !count($this->styles))
		{
			return '';
		}
		
		// Simplify by only dealing with arrays
		if (!is_array($styles))
		{
			$styles = array($styles);
		}
		
		// Should we include the stored styles? 
		if ($override === false && count($this->styles))
		{	
			$styles = array_merge($this->styles, $styles);
		}
		
		// Loop through all of the styles, saving the output links
		$output = '';
		foreach ($styles as $style)
		{
			// Is it part of a package? 
			if (array_key_exists($style, $this->packages) || $style=='all')
			{	
				$output .= $this->render_package($style, 'css') . "\n";
			}
			else 
			{	
				$output .= $this->render_links($style);
			}
		}
		
		//$this->ci->benchmark->mark('css_end');
		
		return $output;
	}
	
	//---------------------------------------------------------------
	
	//---------------------------------------------------------------
	// !JAVASCRIPT FUNCTIONS
	//---------------------------------------------------------------
	
	/*
		Method: add_js 
		
		Accepts either an array or a string with a single js file name
		and appends them to the base scripts in $this->scripts;
		
		The file names should NOT have an extension added on to them.
		
		Parameter: styles - either a string or array of file names.
	*/
	public function add_js($scripts=null) 
	{
		//$this->ci->benchmark->mark('add_js_start');
	
		if (empty($scripts))
		{
			return;
		}
	
		// Handle String values
		if (is_string($scripts))
		{
			$this->external_scripts[] = $scripts;
		} 
		
		// Process arrays
		else if (is_array($scripts) && count($scripts) != 0)
		{
			foreach ($scripts as $script)
			{
				$this->external_scripts[] = $script;
			}
		} 
		
		//$this->ci->benchmark->mark('add_js_end');
	}
	
	//---------------------------------------------------------------
	
	/*
		Method: add_inline_js 
		
		Accepts either an array or a string with a single js file name
		and appends them to the base scripts in $this->scripts;
		
		The file names should NOT have an extension added on to them.
		
		Parameter: styles - either a string or array of file names.
	*/
	public function add_js($scripts=null) 
	{
		//$this->ci->benchmark->mark('add_inline_js_start');
	
		if (empty($scripts))
		{
			return;
		}
	
		// Handle String values
		$this->inline_scripts[] = $scripts . "\n\n";
		
		//$this->ci->benchmark->mark('add_inline_js_end');
	}
	
	//---------------------------------------------------------------
	
	/*
	 	Method: js
	 	
	 	Creates the proper links for inserting javascript links, 
	 	depending on whether devmode is 'dev' or other (test/production).
	 	
	 	The file names should NOT have the extension.
	 	
	 	Parameter: styles - either a string or array of package/file names
	*/
	public function js($scripts=null, $override=false) 
	{
		return 'Javascript Files';		
	}
	
	//---------------------------------------------------------------
	
	//---------------------------------------------------------------
	// !UTILITY FUNCTIONS
	//---------------------------------------------------------------
	
	/*
		Method: render_links
		
		Renders out a selection of css file links.
		
		Parameter: styles - A string or array of file names.
		
		Returns: The string containing the link(s) to the styles.
	*/
	public function render_links($files=null, $type='css') 
	{
		//$this->ci->benchmark->mark('render_links_start');
	
		if (!is_string($files) && !is_array($files))
		{
			return '';
		}
		
		// Render a single link
		if (is_string($files))
		{	
			$cache = dirname(APPPATH) .'/assets/'. $type .'/'. $files .'.'. $type;
			
			if (!is_file($cache) || $this->check_cache($cache, array($files), $type))
			{
				$this->load_helpers();
				
				$content = "\n\n/*\n\tOriginal File: $files\n*/\n\n";
				$content .= $this->ci->load->view('assets/'. $type .'/'. $files, null, true);
				
				write_file($cache, $content);
			}
		
			return $this->build_link($files);
		}
		
		// Render an array of links
		else 
		{
			$links = '';
			
			foreach ($files as $file)
			{
				$links .= $this->render_links($file, $type);
			}
			
			return $links;
		}
	}
	
	//---------------------------------------------------------------
	
	/*
		Method: build_link
		
		Writes out a file to the $assets_folder/css folder, and then
		returns a link to that file.
		
		Parameters: 
			style	- The name of the style file to link to.
			type	- either 'css' or 'js'
		
		Returns: A string containing the html link.
	*/
	public function build_link($style, $type='css') 
	{
		//$this->ci->benchmark->mark('build_link_start');
	
		// Write the file to the asset folder, if it doesn't already exist
		$file = dirname(APPPATH) .'/assets/'. $type .'/'. $style .'.'. $type;
					
		if (!is_file($file))
		{
			write_file($file, $this->ci->load->view('assets/'. $type .'/'. $style, null, true));
		}
	
		if ($type == 'css')
		{
			$link = '<link rel="stylesheet" type="text/css" href="'. base_url() . $this->asset_folder .'css/'. $style . '.css" />' . "\n";
		}
		else 
		{
			$link = 'javascript file';
		}
		
		//$this->ci->benchmark->mark('build_link_end');
		
		return $link;
	}
	
	//---------------------------------------------------------------
	
	/*
		Method: render_package
		
		Takes care of rendering out the links to either css or js files. 
		All of the files are combined into a single file that is then
		stored in the assets folder.
		
		Parameters: 
			name	- The package name
			type	- either 'css' or 'js'
	*/
	public function render_package($name=null, $type=null) 
	{
		//$this->ci->benchmark->mark('render_package_start');
	
		if (!is_string($name) || !is_string($type))
		{
			return;
		}
		
		if ($name != 'all')
		{
			$files = $this->packages;
			$files = $files[$name][$type];
		} else 
		{
			// Name == 'all', so grab a list of all files in the folder.
			$files = get_filenames(APPPATH .'/views/assets/'. $type .'/');
		}
		
		if ($files == false)
		{
			return;
		}
		
		$cache = dirname(APPPATH) .'/assets/'. $type .'/'. $name .'.'. $type;
		
		if (!is_file($cache) || $this->check_cache($cache, $files, $type))
		{		
			$content = '';
			
			// Load our helpers
			$this->load_helpers();
			
			foreach ($files as $file)
			{
				$content .= "\n\n/*\n\tOriginal File: $file\n*/\n\n";
				$content .= $this->ci->load->view('assets/'. $type .'/'. $file, null, true);
			}
			
			write_file($cache, $content);
		}
		
		//$this->ci->benchmark->mark('render_package_end');
		
		return $this->build_link($name, $type);
	}
	
	//---------------------------------------------------------------
	
	//---------------------------------------------------------------
	// !PRIVATE FUNCTIONS
	//---------------------------------------------------------------
	
	/*
		Method: check_cache
		
		Checks the modified dates on the cache file vs the original files.
		
		Parameters:
			$cache_file - string with the full path to cache file
			$files		- an array with the name(s) of the files to check
		
		Returns: 
			true - if the original dates have been modified since cache created
			false - if the cache file is newer
	*/
	private function check_cache($cache_file=null, $files=null, $type='css') 
	{
		if (!is_string($cache_file) && !is_array($files))
		{
			return true;
		}
		
		// Get our cache modified date
		$info = get_file_info($cache_file, 'date');
		$cache_date = $info['date'];
		
		// Check our original files against the cached file
		foreach ($files as $file)
		{
			$info = get_file_info(APPPATH .'views/assets/'. $type .'/'. $file, 'date');

			if ($info['date'] > $cache_date)
			{
				return true;
			}
		}
		
		// We made it through all of the files, so no cache refresh needed.
		return false;
	}
	
	//---------------------------------------------------------------
	
	private function load_helpers() 
	{
		// Grab the helpers to load from the configuration file
		$helpers = $this->ci->config->item('assets.helpers');
		
		if (isset($helpers) && is_array($helpers))
		{
			foreach ($helpers as $helper)
			{
				$this->ci->load->helper($helper);
			}
		}
	}
	
	//---------------------------------------------------------------
	
}


// END Assets class

/* End of file Assets.php */
/* Location: ./application/libraries/Assets.php */