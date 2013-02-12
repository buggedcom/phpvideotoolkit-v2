<?php
	
	/**
	 * This file is part of the PHP Video Toolkit v2 package.
	 *
	 * @author Oliver Lillie (aka buggedcom) <publicmail@buggedcom.co.uk>
	 * @license Dual licensed under MIT and GPLv2
	 * @copyright Copyright (c) 2008 Oliver Lillie <http://www.buggedcom.co.uk>
	 * @package PHPVideoToolkit V2
	 * @version 2.0.0.a
	 * @uses ffmpeg http://ffmpeg.sourceforge.net/
	 */
	
	spl_autoload_register(function($class_name)
	{
		$parts = explode('\\', $class_name);
		$namespace = array_shift($parts);
		if($namespace === 'PHPVideoToolkit')
		{
			$class = array_pop($parts);
			$path = dirname(__FILE__).DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $parts).$class.'.php';
	
			require_once $path;
		}
	});