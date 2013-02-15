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
	 * This is a container class for determining which ffmpeg parser class
	 * to use to be able to correctly parser the data returned by the 
	 * ffmpeg that is installed on the system.
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @author Jorrit Schippers
	 * @package default
	 */
	class FfmpegParser
	{
		protected $_parser;
		
		/**
		 * @access public
		 * @author Oliver Lillie
		 * @param string $ffmpeg_path 
		 * @param string $temp_directory 
		 */
		public function __construct($ffmpeg_path, $temp_directory)
		{
			$parser = new Parser($ffmpeg_path, $temp_directory);
			$format_data = $parser->getRawFormatData();
			if(preg_match_all('/ [DEVAST ]{0,6} [A-Za-z0-9\_]* .*/', $format_data) > 0)
			{
				$this->_parser = new FfmpegParserFormatsArgumentOnly($ffmpeg_path, $temp_directory);
			}
			else
			{
				$this->_parser = new FfmpegParserGeneric($ffmpeg_path, $temp_directory);
			}
		}
		
		/**
		 * Calls any method in the contained $_parser class.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $name 
		 * @param string $arguments 
		 * @return mixed
		 */
		public function __call($name, $arguments)
		{
			if(method_exists($this->_parser, $name) === true)
			{
				return call_user_func_array(array($this->_parser, $name), $arguments);
			}
			else
			{
				// TODO error
			}
		}
	}
