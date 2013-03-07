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
	 * @access public
	 * @author Oliver Lillie
	 * @package default
	 */
	class ImageFormat_Gif extends ImageFormat
	{
		private $_original_save_path;
		
		const UNLIMITED_LOOPS = -1;
		
		public function __construct($input_output_type, Config $config=null)
		{
			parent::__construct($input_output_type, $config);
			
			$this->_format = array_merge($this->_format, array(
				'gif_loop_count' => self::UNLIMITED_LOOPS,
			));
			
			$this->_format_to_command = array_merge($this->_format_to_command, array(
				'gif_loop_count' 			=> '',
			));
			
			if($input_output_type === 'output')
			{
				$this->disableAudio()
					 ->setVideoCodec('gif')
					 ->setFormat('gif');
			}
			
			$this->_restricted_audio_codecs = array();
			$this->_restricted_video_codecs = array('gif');
			
			$this->_original_save_path = null;
		}
		
		public function setLoopCount($loop_count)
		{
			$this->_blockSetOnInputFormat('animated gif loop count');
			
			if($loop_count === null)
			{
				$this->_format['gif_loop_count'] = null;
				return $this;
			}
			
			if($loop_count !== null && $loop_count < -1)
			{
				throw new Exception('The loop count cannot be less than -1. (-1 specifies unlimited looping)');
			}
			
			$this->_format['gif_loop_count'] = (int) $loop_count;
			
			return $this;
		}
		
		public function updateFormatOptions(&$save_path)
		{
			parent::updateFormatOptions($save_path);
			
//			if the save path doesn't have %d in it then we are ouputing an animated gif,
//			otherwise it is assumed that the output is multiple images.
//			If we are going to output an animated gif we must prevent ffmpeg from doing it.
//			This is because ffmpeg creates really shitty animated gifs, which is suprising.
			if(preg_match('/(\%([0-9]*)?index|timecode)/', $save_path, $matches) === 0)
			{
				$this->_original_save_path = $save_path;

//				if the frame rate has not been set, find out what it is and then set it
				if(empty($this->_format['video_frame_rate']) === true)
				{
					$frame_rate = $this->_media_object->getFrameRate();
					$this->setVideoFrameRate(floor($frame_rate));
				}
				
//				as we are outputting frames we want the png format for each frame for best possible output
				$this->_restricted_video_codecs = array('png');
				$this->setVideoCodec('png')
					 ->setFormat('image2');
				$this->_restricted_video_codecs = array('gif');
				
//				update the pathway to include indexed output so that it outputs multiple frames.
				$ext = pathinfo($save_path, PATHINFO_EXTENSION);
				$filename = 'phpvideotoolkit_anigif_'.String::generateRandomAlphaString(5).'_'.basename(substr_replace($save_path, '%12index.png', -(strlen($ext)+1)));
				$save_path = $this->_config->temp_directory.DIRECTORY_SEPARATOR.$filename;
				
//				register the post process to combine the images into an animated gif
				$this->_media_object->registerOutputPostProcess(array($this, 'postProcessCreateAnimatedGif'));
			}
			
			return $this;
		}
		
		public function postProcessCreateAnimatedGif(array $output)
		{
//			create the gif
			$gif = AnimatedGif::createFrom($output, 1/$this->_format['video_frame_rate'], $this->_format['gif_loop_count'], $this->_config);
			
//			break out the dirname incase of relative pathways.
			$name = basename($this->_original_save_path);
			$path = realpath(dirname($this->_original_save_path));
			$save_path = $path.DIRECTORY_SEPARATOR.$name;
			
//			save the gif
			$image = $gif->save($save_path);
			
//			remove tmp frame files
			foreach ($output as $output_image)
			{
				@unlink($output_image->getMediaPath());
			}
			
//			return an updated output
			return $image;
		}
		
		
	}
