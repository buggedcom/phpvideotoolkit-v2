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
	class Video extends Media
	{
		protected $_extracting_frames;
		protected $_extracting_audio;
		
		public function __construct($file_path, $ffmpeg_path, $temp_directory)
		{
			parent::__construct($file_path, $ffmpeg_path, $temp_directory);
			
//			validate this media file is a video file
			$type = $this->getType();
			if($type !== 'video')
			{
				throw new Exception('You cannot use an \\PHPVideoToolkit\\Video for "'.$file_path.'" as the file is not a video file. It is reported to be a '.$type);
			}
			
			$this->_extracting_frames = false;
			$this->_extracting_audio = false;
		}
		
		public function extractAudio()
		{
			$this->_extracting_audio = true;
			
			return $this;
		}
		
		public function extractFrame(Timecode $timecode)
		{
			$this->extractSegment($timecode, $timecode);
			$this->_extracting_frames = true;
			
			return $this;
		}
		
		/**
		 * Process the output format just before the it is compiled into commands.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param Format &$output_format 
		 * @return void
		 */
		protected function _processOutputFormat(Format &$output_format)
		{
			parent::_processOutputFormat($output_format);
			
//			turn off the related options.
			if($this->_extracting_audio === true)
			{
				$output_format->disableVideo();
			}
			if($this->_extracting_frames === true)
			{
				$output_format->disableAudio();
			}
			
//			check for conflictions with having both audio and video disabled.
			$options = $output_format->getFormatOptions();
			if($options['disable_audio'] === true && $options['disable_video'] === true)
			{
				throw new Exception('Unable to process output format to send to ffmpeg as both audio and video are disabled.');
			}
		}
	}
