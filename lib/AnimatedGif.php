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
	 * This class provides generic data parsing for the output from FFmpeg.
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @author Jorrit Schippers
	 * @package default
	 */
	class AnimatedGif
	{
		protected $_transcoder;
		protected $_file_path;
		
		public function __construct($temp_directory, $file_path=null, $transcoder_engine=null)
		{
//			validate temp directory
			if(is_dir($temp_directory) === false)
			{
				throw new Exception('The temp directory does not exist or is not a directory.');
			}
			else if(is_readable($temp_directory) === false)
			{
				throw new Exception('The temp directory is not readable.');
			}
			else if(is_writable($temp_directory) === false)
			{
				throw new Exception('The temp directory is not writeable.');
			}
			$this->_temp_directory = $temp_directory;
			
//			if we have a file, check that it exists, is readable, is an image and is a gif.
			if($file_path !== null)
			{
				$real_file_path = realpath($file_path);
				if($real_file_path === false || is_file($real_file_path) === false)
				{
					throw new Exception('The file does not exist.');
				}
				else if(is_readable($real_file_path) === false)
				{
					throw new Exception('The file is not readable.');
				}
				else if(($image_info = getimagesize($real_file_path)) === false)
				{
					throw new Exception('The file is not an image.');					
				}
				else if($image_info[2] !== IMAGETYPE_GIF)
				{
					throw new Exception('The file is not a gif.');					
				}
			}
			$this->_file_path = $file_path;
			
//			auto detect a transcoder based on order of preference.
			if($transcoder_engine === null)
			{
				$transcoder_preference = array('gifsicle', 'convert', 'php');
				foreach ($transcoder_preference as $transcoder)
				{
					$class = 'AnimatedGifTranscoder'.ucfirst($transcoder);
					if($class::available() === true)
					{
						$transcoder_engine = $transcoder;
						break;
					}
				}
				if($transcoder_engine === null)
				{
					throw new Exception('There are no available transcoders on your system.');
				}
			}
//			validate the transcoder engine if set
			else if(in_array($transcoder_engine, array('php', 'convert', 'gifsicle')) === false)
			{
				throw new Exception('Unrecognised transcoder engine.');
			}
			else
			{
//				create the transcoder and check it's available
				$class = 'AnimatedGifTranscoder'.ucfirst($transcoder_engine);
				if($class::available() === false)
				{
					throw new Exception('The transcoder engine "'.$transcoder_engine.'" is not available on your system.');
				}
				$this->_transcoder = new $class($ffmpeg_path, $temp_directory);
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
			if(method_exists($this->_transcoder, $name) === true)
			{
				return call_user_func_array(array($this->_transcoder, $name), $arguments);
			}
			else
			{
				// TODO error
			}
		}

		/**
		 * Creates a new animated gif object from a selection of files.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_paths 
		 * @param string $frame_delay 
		 * @param string $loop_count 
		 * @return mixed Returns an AnimatedGif object on success, otherwise returns false.
		 */
		public static function createFrom($file_paths, $frame_delay, $loop_count, $temp_directory, $transcoder_engine=null)
		{
			
		}
		
		/**
		 * Expands an animated gif into a list of files.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param string $to_path 
		 * @return mixed Returns an array of file paths on success, otherwise false.
		 */
		public static function expand($file_path, $to_path, $temp_directory, $transcoder_engine=null)
		{
			
		}
	}
