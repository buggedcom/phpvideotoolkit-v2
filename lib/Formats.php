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
	class Formats extends FormatData
	{
		/**
		 * Returns an array of extensions assiciated with this format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $format 
		 * @return mixed Returns false if no matching extensions are found, otherwise returns the list 
		 *	of extensions in an array.
		 */
		public static function toExtensions($format)
		{
			$format = strtolower($format);
			return isset(self::$format_to_extensions[$format]) === true ? self::$format_to_extensions[$format] : false;
		}
		
		/**
		 * Attempts to get a "best guess" extension from all compatible extensions.
		 * The way this works is that if an exact match of the format is that extension, then that is returned,
		 * if not then the first extension in the matched extensions is returned.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $format 
		 * @return mixed Returns false if no extension is found, otherwise the extension is returned as a string.
		 */
		public static function toBestGuessExtension($format)
		{
			$format = strtolower($format);
			$extensions = self::toExtensions($format);
			
			if($extensions === false)
			{
				return false;
			}
			else if(in_array($format, $extensions) === true)
			{
				return $format;
			}
			
			return $extensions[0];
		}
		
	}
