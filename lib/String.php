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
	 
	namespace PHPVideoToolkit;
	
	/**
	 * @access public
	 * @author Oliver Lillie
	 * @package default
	 */
	class String
	{
		/**
		 * Generates a random string.
		 *
		 * @access protected
		 * @author Oliver Lillie
		 * @return string
		 */
		public static function generateRandomString()
		{
			return (rand(10000, 99999).'_'.self::generateRandomAlphaString(5).'_'.time());
		}
		
		/**
		 * Generates a random alphanumeric string.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param integer $length 
		 * @return string
		 */
		public static function generateRandomAlphaString($length) 
		{
		    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
		    $randomString = '';
		    for ($i = 0; $i < $length; $i++)
			{
		        $randomString .= $characters[rand(0, strlen($characters) - 1)];
		    }
		    return $randomString;
		}
		
	}