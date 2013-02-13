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
	class VideoFormat extends AudioFormat
	{
	    const DIMENSION_SAME_AS_SOURCE 	= 'SameAsSource';
		
	    const DIMENSION_SQCIF 	= '128x96';
	    const DIMENSION_QCIF 	= '176x144';
	    const DIMENSION_CIF 	= '352x288';
	    const DIMENSION_4CIF 	= '704x576';
	    const DIMENSION_QQVGA 	= '160x120';
	    const DIMENSION_QVGA 	= '320x240';
	    const DIMENSION_VGA 	= '640x480';
	    const DIMENSION_SVGA 	= '800x600';
	    const DIMENSION_XGA 	= '1024x768';
	    const DIMENSION_UXGA 	= '1600x1200';
	    const DIMENSION_QXGA 	= '2048x1536';
	    const DIMENSION_SXGA 	= '1280x1024';
	    const DIMENSION_QSXGA 	= '2560x2048';
	    const DIMENSION_HSXGA 	= '5120x4096';
	    const DIMENSION_WVGA 	= '852x480';
	    const DIMENSION_WXGA 	= '1366x768';
	    const DIMENSION_WSXGA 	= '1600x1024';
	    const DIMENSION_WUXGA 	= '1920x1200';
	    const DIMENSION_WOXGA 	= '2560x1600';
	    const DIMENSION_WQSXGA 	= '3200x2048';
	    const DIMENSION_WQUXGA 	= '3840x2400';
	    const DIMENSION_WHSXGA 	= '6400x4096';
	    const DIMENSION_WHUXGA 	= '7680x4800';
	    const DIMENSION_CGA 	= '320x200';
	    const DIMENSION_EGA 	= '640x350';
	    const DIMENSION_HD480 	= '852x480';
	    const DIMENSION_HD720 	= '1280x720';
	    const DIMENSION_HD1080 	= '1920x1080';

		public function __construct($ffmpeg_path, $temp_directory, $type='output')
		{
			parent::__construct($ffmpeg_path, $temp_directory, $type);
			
			$this->_format = array_merge($this->_format, array(
				'disable_video' => null,
				'video_codec' => 'copy',
				'video_quality' => null,
				'video_dimensions' => null,
				'video_padding' => null,
				'video_aspect_ratio' => null,
				'video_frame_rate' => null,
				'video_bitrate' => null,
				'video_pixel_format' => null,
			));
			$this->_format_to_command = array_merge($this->_format_to_command, array(
				'disable_video' 			=> '-vn',
				'video_quality' 			=> '-q:v <setting>',
				'video_codec' 				=> '-vcodec <setting>',
				'video_dimensions' 			=> '-s <setting>',
				'video_padding' 			=> '-vf "pad=<horizontal>:<vertical>:<left>:<right>:<colour>"',
				'video_aspect_ratio' 		=> '-aspect <setting>',
				'video_frame_rate' 			=> '-r <setting>',
				'video_bitrate' 			=> '-b:v <setting>',
				'video_pixel_format' 		=> '-pix_fmt <setting>',
			));
		}
		
		public function disableVideo()
		{
			if($this->_type === 'input')
			{
				throw new Exception('Video cannot be disabled on an input '.get_class($this).'.');
			}
			
			$this->_format['disable_video'] = true;
		}
		
		public function enableVideo()
		{
			$this->_format['disable_video'] = false;
		}
		
		public function setVideoCodec($video_codec)
		{
			if($this->_type === 'input')
			{
				throw new Exception('The video codec cannot be set on an input '.get_class($this).'.');
			}
			
//			validate the video codecs that are available from ffmpeg.
			$codecs = array_keys($this->getCodecs('video'));
			if(in_array($video_codec, $codecs) === false)
			{
				throw new Exception('Unrecognised audio codec "'.$video_codec.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoCodec');
			}
			
			$this->_format['video_codec'] = $video_codec;
			return $this;
		}
		
		public function setVideoDimensions($width, $height=null, $add_padding_adjustments=true, $force_aspect_ratio=false)
		{
			if($height === null)
			{
				if(in_array($width, array(self::DIMENSION_SAME_AS_SOURCE, self::DIMENSION_SQCIF, self::DIMENSION_QCIF, self::DIMENSION_CIF, self::DIMENSION_4CIF, self::DIMENSION_QQVGA, self::DIMENSION_QVGA, self::DIMENSION_VGA, self::DIMENSION_SVGA, self::DIMENSION_XGA, self::DIMENSION_UXGA, self::DIMENSION_QXGA, self::DIMENSION_SXGA, self::DIMENSION_QSXGA, self::DIMENSION_HSXGA, self::DIMENSION_WVGA, self::DIMENSION_WXGA, self::DIMENSION_WSXGA, self::DIMENSION_WUXGA, self::DIMENSION_WOXGA, self::DIMENSION_WQSXGA, self::DIMENSION_WQUXGA, self::DIMENSION_WHSXGA, self::DIMENSION_WHUXGA, self::DIMENSION_CGA, self::DIMENSION_EGA, self::DIMENSION_HD480, self::DIMENSION_HD720, self::DIMENSION_HD1080)) === false)
				{
					throw new Exception();
				}
				
//				if we have the same as source dimensions...
				if($width === self::DIMENSION_SAME_AS_SOURCE)
				{
					$this->_format['video_dimensions'] = 'sas';
					return $this;
				}
				else
				{
					$parts = explode('x', $width);
					$width = (int) $parts[0];
					$height = (int) $parts[1];
				}
			}
			else 
			{
				$width = (int) $width;
				$height = (int) $height;
			}
			
			if(preg_match('/^[0-9]+x[0-9]+$/', $width) === 0)
			{
				throw new Exception('Unrecognised dimensions "'.$width.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoDimensions');
			}
			
			$this->_format['video_dimensions'] = array(
				'width' => $width,
				'height' => $height,
				'auto_padding_adjust' => $add_padding_adjustments,
				'force_aspect' => $force_aspect_ratio,
			);
			return $this;
		}
		
		public function setVideoPadding($vertical, $horizontal, $left, $right, $colour)
		{
			// TODO validate 
			$this->_format['video_padding'] = array(
				'vertical' => $vertical,
				'horizontal' => $horizontal,
				'left' => $left,
				'right' => $right,
				'colour' => $colour,
			);
			return $this;
		}
		
		public function setVideoAspectRatio($aspect_ratio)
		{
			if($this->_type === 'input')
			{
				throw new Exception('The video aspect ratio cannot be set on an input '.get_class($this).'.');
			}
			
		    if(preg_match('/^[0-9]+.[0-9]+$/', $aspect_ratio) === 0 && preg_match('/^[0-9]+:[0-9]+$/', $aspect_ratio) === 0)
			{
				throw new Exception('Unrecognised aspect ratio "'.$aspect_ratio.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoAspectRatio');
		    }
			
			$this->_format['video_aspect_ratio'] = $aspect_ratio;
			return $this;
		}
		
		public function setVideoFrameRate($frame_rate)
		{
			if($frame_rate < 1)
			{
				throw new Exception('Unrecognised frame rate "'.$frame_rate.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoFrameRate');
			}
			
			$this->_format['video_frame_rate'] = $frame_rate;
			return $this;
		}
		
		public function setVideoBitrate()
		{
			if(preg_match('/^[0-9]+k$/', $bitrate) > 0)
			{
				$this->_format['video_bitrate'] = $bitrate;
				return $this;
			}
			
			throw new Exception('Unrecognised video bitrate "'.$bitrate.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoBitrate');
		}
		
		public function setPixelFormat($pixel_format)
		{
			$valid_pixel_formats = $this->getPixelFormats();
			if(in_array($pixel_format, array_keys($valid_pixel_formats)) === true)
			{
				$this->_format['video_pixel_format'] = $format;
				return $this;
			}
			
			throw new Exception('Unrecognised pixel format "'.$pixel_format.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setPixelFormat');
		}
		
		
		public function setVideoQuality($quality)
		{
			if($this->_type === 'input')
			{
				throw new Exception('The video quality cannot be set on an input '.get_class($this).'.');
			}
			
// 			interpret quality into ffmpeg value
			$quality = 31 - round(($quality / 100) * 31);
			if($quality > 31 || $quality < 1)
			{
				throw new Exception('Unrecognised quality "'.$quality.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setQuality');
			}
			
			$this->_format['video_quality'] = $quality;
			return $this;
		}
		
	}
