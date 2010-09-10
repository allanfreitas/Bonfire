<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	Bonfire CSS3 Helpers
	
	Author: Lonnie Ezell
*/

//---------------------------------------------------------------

/*
	Function: css_round
	
	Creates the full-complement of border-radius commands to handle
	all browsers supporting css3.
	
	Parameters:
		size	- a string with the correct radius to apply.
*/
if (!function_exists('css_round'))
{
	function css_round($size=null)
	{
		if (is_null($size))
		{
			return;
		}
	
		echo "-moz-border-radius: $size;\n";
		echo "\t-webkit-border-radius: $size;\n";
		echo "\tborder-radius: $size;\n";
	}
}

//---------------------------------------------------------------

/*
	Function: css_round_corner
	
	Creates the full-complement of single border-radius commands
	to handle all browsers supporting css3.
	
	Parameters:
		corner	- a string with the corner name (i.e. - bottom-left)
		size	- a string with the correct radius to apply
*/
if (!function_exists('css_round_corner'))
{
	function css_round_corner($corner=null, $size=null)
	{
		if (empty($corner) || empty($size))
		{
			return;
		}
		
		$moz_corner = str_replace('-', '', $corner);

		echo "\t-moz-border-radius-$moz_corner: $size;\n";
		echo "\t-webkit-border-$corner-radius: $size;\n";
		echo "\tborder-$corner-radius: $size;\n";
	}
}

//---------------------------------------------------------------