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
	class AnimatedGifTranscoderGifsicle extends AnimatedGifTranscoderAbstract
	{
		protected $_tidy_up_gifs;
		
		public function __construct(Config $config=null)
		{
			parent::__construct($config);
			
			$this->_tidy_up_gifs = array();
		}
		
		public function __destruct()
		{
//			tidy up any converted image to gifs.
			if(empty($this->_tidy_up_gifs) === false)
			{
				foreach ($this->_tidy_up_gifs as $path)
				{
					@unlink($path);
				}
				$this->_tidy_up_gifs = array();
			}
		}
		
		public function addFrame(Image $image)
		{
			$frame_path = $image->getMediaPath();
			$data = getimagesize($frame_path);
			
//			gifsicle doesn't work with none gif images so we must convert a frame to a gif
//			if it is not a gif.
			if($data['mime'] !== 'image/gif')
			{
				$gif_path = $this->_convertFrameToGif($frame_path);
			}
			else
			{
				$gif_path = $frame_path;
			}
			
			array_push($this->_frames, $gif_path);
			
			return $this;
		}
		
		protected function _convertFrameToGif($frame_path)
		{
			$gif_path = null;
			
//			first try with imagemagick convert as imagemaigk produces better gifs
			if($this->_config->convert)
			{
				$process = new ProcessBuilder('convert', $this->_config);
				$exec = $process->add($frame_path)
								->add($frame_path.'.convert-convert.gif')
								->getExecBuffer();
				$exec->setBlocking(true)
					 ->execute();
				if($exec->hasError() === true)
				{
					throw new Exception('When attempting to convert "'.$frame_path.'" to a gif, convert encountered an error. Convert reported: '.$exec->getLastLine());
				}
				
				$gif_path = $frame_path.'.convert-convert.gif';
			}
			
//			if we still have no gif path then try with php gd.
			if(empty($gif_path) === true)
			{
				if(function_exists('imagegif') === false)
				{
					throw new Exception('PHP GD\'s function `imagegif` is not available, as a result the frame could not be added.');
				}
				
				$im = false;
				$data = getimagesize($frame_path);
				switch($data['mime'])
				{
					case 'image/jpeg' :
						$im = @imagecreatefromjpeg($frame_path);
						break;
					case 'image/png' :
						$im = @imagecreatefrompng($frame_path);
						break;
					case 'image/xbm' :
						$im = @imagecreatefromwbmp($frame_path);
						break;
					case 'image/xpm' :
						$im = @imagecreatefromxpm($frame_path);
						break;
				}
				if($im === false)
				{
					throw new Exception('Unsupported image type.');
				}
				
//				save as a gif
				$gif_path = $frame_path.'.convert-php.gif';
				if(imagegif($im, $gif_path) === false)
				{
					throw new Exception('Unable to convert frame to gif using PHP GD.');
				}
				imagedestroy($im);
			}
			
			if(empty($gif_path) === true)
			{
				throw new Exception('Unable to convert frame to gif.');
			}
			else if(is_file($gif_path) === false)
			{
				throw new Exception('Unable to convert frame to gif, however the gif conversion path was set.');
			}
			else if(filesize($gif_path) === 0)
			{
				throw new Exception('Unable to convert frame to gif, as a gif was produced, however it was a zero byte file.');
			}
			
			array_push($this->_tidy_up_gifs, $gif_path);
			
			return $gif_path;
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
			parent::save($save_path, $frame_delay);
			
//			build the gifsicle process
			$gifsicle_process = new ProcessBuilder('gifsicle', $this->_config);
			
//			add in all the frames
			foreach ($this->_frames as $path)
			{
				$gifsicle_process->add($path);
			}
			
//			set the looping count
			if($this->_loop_count === AnimatedGif::UNLIMITED_LOOPS)
			{
				$gifsicle_process->add('-l');
			}
			else
			{
				$gifsicle_process->add('-l')->add($this->_loop_count);
			}
			
//			set the frame duration
			//$gifsicle_process->add('-d')->add($frame_delay*1000);

//			add the output path
			$gifsicle_process->add('-o')->add($save_path);
			
//			execute the process.
			$exec = $gifsicle_process->getExecBuffer();
			$exec->setBlocking(true)
				 ->execute();
			
			$this->__destruct();

//			check for any gifsicle errors
			if($exec->hasError() === true)
			{
				throw new FfmpegProcessPostProcessException('AnimatedGif save using `gifsicle` save "'.$save_path.'" failed. Any additional gifsicle message follows: 
'.$exec->getBuffer());
			}
			
			return new Image($save_path, $this->_config);
		}
		
		public static function available(Config $config)
		{
			if($config->gifsicle === null)
			{
				return false;
			}
			
			try
			{
				Binary::locate($config->gifsicle);
				return true;
			}
			catch(Excetion $e)
			{
				return false;
			}
		}
		
	}
