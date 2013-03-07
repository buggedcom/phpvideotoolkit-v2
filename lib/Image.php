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
	 * This class provides generic data parsing for the output from FFmpeg from specific
	 * media files. Parts of the code borrow heavily from Jorrit Schippers version of 
	 * PHPVideoToolkit v 0.1.9.
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @author Jorrit Schippers
	 * @package default
	 */
	class Image extends Media
	{
		public function __construct($video_file_path, Config $config=null, ImageFormat $video_input_format=null, $ensure_image_file=true)
		{
			parent::__construct($video_file_path, $config, $video_input_format);
			
//			validate this media file is a image file
			if($ensure_image_file === true && $this->_validateMedia('image') === false)
			{
				throw new Exception('You cannot use an instance of '.get_class($this).' for "'.$image_file_path.'" as the file is not a image file. It is reported to be a '.$type);
			}
		}
		
		/**
		 * Returns the default (empty) input format for the type of media object this class is.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $type Either input for an input format or output for an output format.
		 * @return Format
		 */
		public function getDefaultFormat($type, $format)
		{
			return $this->_getDefaultFormat($type, 'ImageFormat', $format);
		}
		
		/**
		 * Returns any video information about the file if available.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return mixed Returns an array of found data, otherwise returns null.
		 */
		public function readDimensions($read_from_cache=true)
		{
			$video_data = parent::readVideoComponent($read_from_cache);
			return $video_data['dimensions'];
		}
		
		/**
		 * Returns any video information about the file if available.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return mixed Returns an array of found data, otherwise returns null.
		 */
		public function readFrameRate($read_from_cache=true)
		{
			$video_data = parent::readVideoComponent($read_from_cache);
			return $video_data['frame_rate'];
		}
	}

