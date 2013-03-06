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
	class FfmpegParser //extends Loggable
	{
		protected $_parser;
		protected $_config;
		
		/**
		 * @access public
		 * @author Oliver Lillie
		 * @param string $ffmpeg_path 
		 * @param string $temp_directory 
		 */
		public function __construct(Config $config=null)
		{
			$this->_config = $config === null ? Config::getInstance() : $config;
			$this->_parser = null;
		}
		
		/**
		 * Gets the specific ffmpeg parser to use.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		protected function _getParser()
		{
			$parser = new Parser($this->_config);
			$format_data = $parser->getRawFormatData();
			if(strpos($format_data, 'Codecs:') !== false)
			{
				$this->_parser = new FfmpegParserFormatsArgumentOnly($this->_config);
			}
			else
			{
				$this->_parser = new FfmpegParserGeneric($this->_config);
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
			if($this->_parser === null)
			{
				$this->_getParser();
			}
			
			if(method_exists($this->_parser, $name) === true)
			{
				return call_user_func_array(array($this->_parser, $name), $arguments);
			}
			else
			{
				throw new Exception('`'.$name.'` is not a valid parser function.');
			}
		}
	}
