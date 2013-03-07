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
	 
	use GifCreator; 
	 
	/**
	 * This class provides generic data parsing for the output from FFmpeg.
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @author Jorrit Schippers
	 * @package default
	 */
	class AnimatedGifTranscoderPhp extends AnimatedGifTranscoderAbstract
	{
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
			parent::save($save_path, $frame_delay);
			
//			build the gif creator process
			$gc = new \GifCreator\GifCreator();
			
//			add in all the frames
			$durations = array();
			$frame_duration = $frame_delay*100;
			foreach ($this->_frames as $path)
			{
				array_push($durations, $frame_duration);
			}
			$gc->create($this->_frames, $durations, $this->_loop_count === AnimatedGif::UNLIMITED_LOOPS ? '0' : $this->_loop_count+1);
			$gif_data = $gc->getGif();
			
//			check for errors or put the data into the file.
			if(empty($gif_data) === true || file_put_contents($save_path, $gif_data) === false)
			{
				throw new FfmpegProcessPostProcessException('AnimatedGif save using `php` "'.$save_path.'" failed.');
			}
			
			return new Image($save_path, $this->_config);
		}
		
		public static function available(Config $config)
		{
			return function_exists('imagegif') && is_file(dirname(dirname(__FILE__)).'/vendor/sybio/gif-creator/src/GifCreator/GifCreator.php');
		}
	}
