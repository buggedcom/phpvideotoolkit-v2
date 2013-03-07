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
	abstract class AnimatedGifTranscoderAbstract//implements AnimatedGifTranscoderInterface
	{
		protected $_config;
		protected $_frames;
		protected $_loop_count;
		
		public function __construct(Config $config=null)
		{
			$this->_config = $config === null ? Config::getInstance() : $config;
			$this->_frames = array();
			$this->_loop_count = AnimatedGif::UNLIMITED_LOOPS;
		}
		
		/**
		 * Adds a frame to the current timeline.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param string $frame_delay 
		 * @return boolean
		 */
		public function addFrame(Image $image)
		{
			array_push($this->_frames, $image->getMediaPath());
			
			return $this;
		}
		
		/**
		 * Adds a frame to the current timeline.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param string $frame_delay 
		 * @return boolean
		 */
		public function setLoopCount($loop_count)
		{
			if($loop_count !== null && $loop_count < -1)
			{
				throw new Exception('The loop count cannot be less than -1. (-1 specifies unlimited looping)');
			}
			$this->_loop_count = (int) $loop_count;
			
			return $this;
		}
		
		/**
		 * Saves the animated gif.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $save_path
		 * @param float $frame_delay The delay of each frame.
		 * @return Image
		 */
		public function save($save_path, $frame_delay=0.1)
		{
			if(empty($this->_frames) === true)
			{
				throw new Exception('At least one frame must be added in order to save an animated gif.');
			}
			
			if($frame_delay < 0.001)
			{
				throw new Exception('The frame delay must at least be 0.001.');
			}
			
			if(is_file($save_path) === true)
			{
				throw new Exception('The save path "'.$save_path.'" already exists.');
			}
		}
	}
