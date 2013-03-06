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
	class VideoFormat_H264 extends VideoFormat
	{
		protected $_restricted_video_presets;
		
		protected $_post_process_qt_faststart;
		protected $_enforce_qt_faststart_success;

		public function __construct($input_output_type, Config $config=null)
		{
			parent::__construct($input_output_type, $config);
			
			$this->_format = array_merge($this->_format, array(
				'h264_preset' => null,
				'h264_tune' => null,
				'h264_constant_quantization' => null,
			));
			$this->_format_to_command = array_merge($this->_format_to_command, array(
				'h264_preset' => '-preset <setting>',
				'h264_tune' => '-tune <setting>',
				'h264_constant_quantization' => '-qp <setting>',
			));
			
			$this->_restricted_video_presets = null;
			
			if($input_output_type === 'output')
			{
				$this->setAudioCodec('mp3')
					 ->setVideoCodec('h264')
					 ->setFormat('h264');
			}
			
//			both enable meta data injection and then force 
			$this->forceQtFastStartSuccess();
			$this->enableQtFastStart();
		}
		
		public function enableQtFastStart()
		{
			$this->_post_process_qt_faststart = true;
		}
		
		public function disableQtFastStart()
		{
			$this->_post_process_qt_faststart = false;
		}
		
		public function allowQtFastStartFailure()
		{
			$this->_enforce_qt_faststart_success = false;
		}
		
		public function forceQtFastStartSuccess()
		{
			$this->_enforce_qt_faststart_success = true;
		}

		public function setH264Preset($preset=null)
		{
			$this->_blockSetOnInputFormat('h264 preset');
			
			if($preset === null)
			{
				$this->_format['h264_preset'] = null;
				return $this;
			}
			
			if(in_array($preset, array('ultrafast', 'superfast', 'veryfast', 'faster', 'fast', 'medium', 'slow', 'slower', 'veryslow', 'placebo')) === false)
			{
				throw new Exception('Unrecognised h264 preset "'.$preset.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setH264Preset');
			}
			
			$this->_format['h264_preset'] = $preset;
			return $this;
		}
		
		public function setH264Tune($tune=null)
		{
			$this->_blockSetOnInputFormat('h264 tune');
			
			if($tune === null)
			{
				$this->_format['h264_tune'] = null;
				return $this;
			}
			
			if(in_array($tune, array('film', 'animation', 'grain', 'stillimage', 'psnr', 'ssim', 'fastdecode', 'zerolatency')) === false)
			{
				throw new Exception('Unrecognised h264 preset "'.$preset.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setH264Tune');
			}
			
			$this->_format['h264_tune'] = $tune;
			return $this;
		}
		
		public function enableH264LosslessEncoding()
		{
			$this->_format['h264_constant_quantization'] = 0;
			return $this;
		}
		
		public function disableH264LosslessEncoding()
		{
			$this->_format['h264_constant_quantization'] = null;
			return $this;
		}
		
		public function updateFormatOptions()
		{
			parent::updateFormatOptions();
			
//			assign a post process so that qt-faststart (https://ffmpeg.org/trac/ffmpeg/wiki/UbuntuCompilationGuide#qt-faststart) changes the qt atom to allow fast streaming.
			if($this->_post_process_meta_data_injection === true)
			{
				$this->_media_object->registerOutputPostProcess(array($this, 'postProcessFastStart'));
			}
			
			return $this;
		}
		
		/**
		 * Specifically for creating fast starting files.
		 * however it can also be used as a standalone function call from the H264Format object.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param Media $media 
		 * @return Media
		 */
		public function postProcessMetaData(Media $media)
		{
//			set the yamdi input and output options.
			$output = $media->getMediaPath();
			$temp_output = $output.'.qtfaststart.'.pathinfo($output, PATHINFO_EXTENSION);

//			build the qtfaststart process
			$qtfaststart_process = new ProcessBuilder('qt-faststart', $this->_config);
			$exec = $qtfaststart_process
						  ->add($output)
						  ->add($temp_output)
						  ->getExecBuffer();
				
//			execute the process.
			$exec->setBlocking(true)
				 ->execute();
				
//			check for any qt-faststart errors
			if($exec->hasError() === true)
			{
				if(is_file($temp_output) === true)
				{
					@unlink($temp_output);
				}
				if($this->_enforce_qt_faststart_success === true)
				{
					@unlink($output);
					throw new FfmpegProcessPostProcessException('qt-faststart post processing of "'.$output.'" failed. The output file has been removed. Any additional qt-faststart message follows: 
'.$exec->getBuffer());
				}
				// TODO, log or exception not sure as the original file is ok.
			}
			else
			{
//				nope everything went ok. so delete ffmpeg file, and then rename yamdi file to that of the original.
				unlink($output);
				rename($temp_output, $output);
			}
			
			return $media;
		}
	}
