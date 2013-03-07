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
	class AnimatedGifTranscoderConvert extends AnimatedGifTranscoderAbstract
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
			
//			build the gifsicle process
			$process = new ProcessBuilder('convert', $this->_config);
			
//			set the frame duration
			$process->add('-delay')->add($frame_delay*100);
			$process->add('-loop')->add($this->_loop_count === AnimatedGif::UNLIMITED_LOOPS ? '0' : $this->_loop_count+1);

//			add in all the frames
			foreach ($this->_frames as $path)
			{
				$process->add($path);
			}
			
//			add the output path
			$process->add($save_path);
			
//			execute the process.
			$exec = $process->getExecBuffer();
			$exec->setBlocking(true)
				 ->execute();
			
//			check for any gifsicle errors
			if($exec->hasError() === true)
			{
				throw new FfmpegProcessPostProcessException('AnimatedGif save using `convert` "'.$save_path.'" failed. Any additional convert message follows: 
'.$exec->getBuffer());
			}
			
			return new Image($save_path, $this->_config);
		}
		
		public static function available(Config $config)
		{
			if($config->convert === null)
			{
				return false;
			}
			
			try
			{
				Binary::locate($config->convert);
				return true;
			}
			catch(Excetion $e)
			{
				return false;
			}
		}
	}
