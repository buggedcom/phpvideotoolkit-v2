<?php
	
	if(defined('ENT_SUBSTITUTE') === false)
	{
		define('ENT_SUBSTITUTE', 'ENT_SUBSTITUTE');
	}
	
	/**
	 * Shortcut function for htmlspecialchars
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @param string $value 
	 * @param string $quotes 
	 * @return void
	 */
	function HTML($value, $quotes=ENT_QUOTES)
	{
		return htmlspecialchars($value, $quotes | ENT_SUBSTITUTE, 'UTF-8');
	}

	/**
	 * shortcut function for json_encode
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @return void
	 */
	function JS($value)
	{
		return json_encode($value);
	}

