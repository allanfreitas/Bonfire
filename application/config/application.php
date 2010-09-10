<?php

//---------------------------------------------------------------
// !TEMPLATE SETTINGS
//---------------------------------------------------------------

/*
|--------------------------------------------------------------------
| MESSAGE TEMPLATE
|--------------------------------------------------------------------
| This is the template that Ocular will use when displaying messages
| through the message() function. 
|
| To set the class for the type of message (error, success, etc),
| the {type} placeholder will be replaced. The message will replace 
| the {message} placeholder.
|
*/
$config['template.message_template'] =<<<EOD
	<div class="notification {type}">
		<div>{message}</div>
	</div>
EOD;



//---------------------------------------------------------------
// !ASSET SETTINGS
//---------------------------------------------------------------

/*
|--------------------------------------------------------------------
| PACKAGES
|--------------------------------------------------------------------
| Packages allow you to create collections of css/js files. This makes
| it simple to combine groups of files for separating front and backend files.
| Also convenient for modules
|
| Packages should be defined in the following format: 
|	$config['assets.packages'] = array(
|		'test'	=> array(
|			'css'	=> array('style1', 'style2'),
|			'js'	=> array()
|		)
|	);
*/
$config['assets.packages'] = array(
	'test'	=> array(
		'css'	=> array('style', 'style2'),
		'js'	=> array()
	)
);


/*
|--------------------------------------------------------------------
| ASSET HELPERS
|--------------------------------------------------------------------
| This is an array of helpers to load to make sure they're available
| to any asset files that are loaded. This makes it easy to load
| css helpers, etc when needed.
|
*/
$config['assets.helpers'] = array('assets/css');

//---------------------------------------------------------------
// !CACHE SETTINGS
//---------------------------------------------------------------

