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
		const UNLIMITED_LOOPS = -1;
		
		protected $_transcoder;
		protected $_file_path;
		protected $_config;
		
		public function __construct($gif_path=null, Config $config=null)
		{
			$this->_config = $config === null ? Config::getInstance() : $config;
			
//			if we have a file, check that it exists, is readable, is an image and is a gif.
			if($gif_path !== null)
			{
				$real_file_path = realpath($gif_path);
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
				$gif_path = $real_file_path;
			}
			$this->_file_path = $gif_path;
			
//			validate the transcoder engine if set
			if(in_array($this->_config->gif_transcoder, array('gifsicle', 'convert', 'php', null)) === false)
			{
				throw new Exception('Unrecognised transcoder engine.');
			}
			
//			auto detect a transcoder based on order of preference.
			$transcoder_engine = null;
			if($this->_config->gif_transcoder === null)
			{
				$transcoder_preference = array('gifsicle', 'convert', 'php');
				foreach ($transcoder_preference as $transcoder)
				{
					$transcoder_class = '\\PHPVideoToolkit\\AnimatedGifTranscoder'.ucfirst($transcoder);
					if(call_user_func(array($transcoder_class, 'available'), $this->_config) === true)
					{
						$transcoder_engine = $transcoder_class;
						break;
					}
				}
			}
			else
			{
//				create the transcoder and check it's available
				$transcoder_class = '\\PHPVideoToolkit\\AnimatedGifTranscoder'.ucfirst($this->_config->gif_transcoder);
				if(call_user_func(array($transcoder_class, 'available'), $this->_config) === false)
				{
					throw new Exception('The transcoder engine "'.$this->_config->gif_transcoder.'" is not available on your system.');
				}
				$transcoder_engine = $transcoder_class;
			}
			if($transcoder_engine === null)
			{
				throw new Exception('There are no available transcoders on your system.');
			}
			
			$this->_transcoder = new $transcoder_engine($this->_config);
		}
		
		public function getFilePath()
		{
			return $this->_file_path;
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
				throw new Exception('`'.$name.'` is not a valid animated gif transcoder function.');
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
		public static function createFrom(array $image_object_array, $frame_delay, $loop_count=self::UNLIMITED_LOOPS, Config $config=null)
		{
			if(empty($image_object_array) === true)
			{
				throw new Exception('At least one file path must be specified when creating an animated gif from AnimatedGif::createFrom.');
			}
			if($frame_delay <= 0)
			{
				throw new Exception('The frame delay must be greater than 0.');
			}

//			create a new gif and add all the frames.
			$gif = new self(null, $config);
			foreach ($image_object_array as $key=>$image)
			{
				if(is_object($image) === false || get_class($image) !== 'PHPVideoToolkit\\Image')
				{
					throw new Exception('The image at key '.$key.' is not an \\PHPVideoToolkit\\Image object. Each frame must be an Image object.');
				}
				
				$gif->addFrame($image, $frame_delay);
			}
			
//			set the loop count
			$gif->setLoopCount($loop_count);
			
			return $gif;
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
		public static function expand($file_path, $to_path, Config $config=null)
		{
			
		}
	}
