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
	}
