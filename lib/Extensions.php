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
	class Extensions extends FormatData
	{
		static $extensions_to_formats = null;
		
		/**
		 * Returns an array of extensions assiciated with this format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $format 
		 * @return mixed Returns false if no matching extensions are found, otherwise returns the list 
		 *	of extensions in an array.
		 */
		public static function toFormats($extension)
		{
			self::_convertFormatHashToExtensionHash();
			
			$extension = strtolower($extension);
			return isset(self::$extensions_to_formats[$extension]) === true ? self::$extensions_to_formats[$extension] : false;
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
		public static function toBestGuessExtension($extension)
		{
			self::_convertFormatHashToExtensionHash();

			$extension = strtolower($extension);
			$formats = self::toFormats($extension);
			
			if($formats === false)
			{
				return false;
			}
			else if(in_array($extension, $formats) === true)
			{
				return $extension;
			}
			
			return $formats[0];
		}
		
		/**
		 * Converts the format to extension data into extension to format data.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		protected static function _convertFormatHashToExtensionHash()
		{
			if(self::$extensions_to_format === null)
			{
				self::$extensions_to_format = array();
				foreach (self::$format_to_extensions as $format => $extensions)
				{
					foreach ($extensions as $ext)
					{
						if(isset(self::$extensions_to_formats[$ext]) === false)
						{
							self::$extensions_to_formats[$ext] = array();
						}
						array_push(self::$extensions_to_formats[$ext], $format);
					}
				}
			}
		}
		
	}
