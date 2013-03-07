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
		
		protected $_restricted_video_bitrates;
		protected $_restricted_video_codecs;
		protected $_restricted_video_pixel_formats;
		protected $_restricted_video_frame_rates;

		public function __construct($input_output_type, Config $config=null)
		{
			parent::__construct($input_output_type, $config);
			
			$this->_format = array_merge($this->_format, array(
				'disable_video' => null,
				'video_codec' => null,
				'video_quality' => null,
				'video_dimensions' => null,
				'video_scale' => null,
				'video_padding' => null,
				'video_aspect_ratio' => null,
				'video_frame_rate' => null,
				'video_bitrate' => null,
				'video_pixel_format' => null,
				'video_rotation' => null,
				'video_flip_horizontal' => null,
				'video_flip_vertical' => null,
				'video_max_frames' => null,
				'video_filters' => null,
			));
			$this->_format_to_command = array_merge($this->_format_to_command, array(
				'disable_video' 			=> '-vn',
				'video_quality' 			=> '-q:v <setting>',
				'video_codec' 				=> '-vcodec <setting>',
				'video_dimensions' 			=> '-s <width>x<height>',
				'video_scale' 				=> '-vf scale=<width>:<height>',
				'video_padding' 			=> '-vf pad=<width>:<height>:<x>:<y>:<colour>',
				'video_aspect_ratio' 		=> '-aspect <ratio>',
				'video_frame_rate' 			=> '-r <setting>',
				'video_bitrate' 			=> '-b:v <setting>',
				'video_pixel_format' 		=> '-pix_fmt <setting>',
				'video_rotation' 			=> '-vf transpose=<setting>',
				'video_flip_horizontal' 	=> '-vf hflip',
				'video_flip_vertical' 		=> '-vf vflip',
				'video_max_frames' 			=> '-vframes <setting>',
			));
			
			$this->_restricted_video_bitrates = null;
			$this->_restricted_video_codecs = null;
			$this->_restricted_video_pixel_formats = null;
			$this->_restricted_video_frame_rates = null;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function updateFormatOptions(&$save_path)
		{
			parent::updateFormatOptions($save_path);
			
//			if we have a rotation and it's set to true then we must autodetect the rotation according to the
//			meta data available.
//			!IMPORTANT that auto orientation is done before any automatic flipping.
			if(empty($this->_format['video_rotation']) === false && $this->_format['video_rotation'] === true)
			{
				$video_data = $this->_media_object->readVideoComponent();
				if(empty($video_data['rotation']) === false)
				{
					$current_rotation = (int) $video_data['rotation'];
					$this->setVideoRotation(-$current_rotation);
					$this->addCommand('-metadata:s:v', 'rotate=""');
				}
				else
				{
					$this->_format['video_rotation'] = null;
				}
			}
			
//			if video padding has been set we might need to update the width/height dimensions of the pad filter
			if(empty($this->_format['video_padding']) === false)
			{
				$padding = $this->_format['video_padding'];
				if(empty($padding['width']) === true || empty($padding['height']) === true)
				{
					$this->setVideoPadding($padding['_padding']['top'], $padding['_padding']['right'], $padding['_padding']['bottom'], $padding['_padding']['left'], $padding['width'], $padding['height'], $padding['colour']);
				}
			}
			
			// TODO expand the video_filters format data
			if(empty($this->_format['video_filters']) === false)
			{
				
			}

			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param VideoFilter $filter 
		 * @return void
		 */
		public function addVideoFilter(VideoFilter $filter)
		{
			$this->_blockSetOnInputFormat('video filter');
			
			$this->_setFilter('video_filters', $filter);
			
			return $this;
		}
		
		/**
		 * Enables FFmpegs' two pass output encoding.
		 * Two pass output encoding is typically has better output.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function enableTwoPassEncoding()
		{
			
		}

		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function disableVideo()
		{
			if($this->_type === 'input')
			{
				throw new Exception('Video cannot be disabled on an input '.get_class($this).'.');
			}
			
			$this->_format['disable_video'] = true;
			
			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function enableVideo()
		{
			$this->_format['disable_video'] = false;
			
			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $video_codec 
		 * @return void
		 */
		public function setVideoCodec($video_codec)
		{
			$this->_blockSetOnInputFormat('video codec');
			
			if($video_codec === null)
			{
				$this->_format['video_codec'] = null;
				return $this;
			}
			
//			get codecs and add special case for copy as it is not included in the codec list but is valid
			$codecs = $this->getCodecs('video');
			$codecs['copy'] = 1;
			
//			work around for h264/libx264 codec names. Thanks to Jorrit Schippers for this one.
			if(in_array($video_codec, array('h264', 'libx264')) === true)
			{
				$video_codec = isset($codecs['libx264']) === true ? 'libx264' : 'h264';
			}
//			work around for theora/libtheora names
			else if(in_array($video_codec, array('theora', 'libtheora')) === true)
			{
				$video_codec = isset($codecs['libtheora']) === true ? 'libtheora' : 'theora';
			}
//			work around for vp8/libvpx names
			else if(in_array($video_codec, array('vp8', 'libvpx')) === true)
			{
				$video_codec = isset($codecs['libvpx']) === true ? 'libvpx' : 'vp8';
			}
			
//			validate the video codecs that are available from ffmpeg.
			if(isset($codecs[$video_codec]) === false)
			{
				throw new Exception('Unrecognised video codec "'.$video_codec.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoCodec');
			}
			
//			now check the class settings to see if restricted codecs have been set and have to be obeys
			if($this->_restricted_video_codecs !== null)
			{
				if(in_array($video_codec, $this->_restricted_video_codecs) === false)
				{
					throw new Exception('The video codec "'.$video_codec.'" cannot be set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoCodec. Please select one of the following codecs: '.implode(', ', $this->_restricted_video_codecs));
				}
			}
			
			$this->_format['video_codec'] = $video_codec;
			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $width 
		 * @param string $height 
		 * @param string $auto_adjust_dimensions_to_optimal 
		 * @param string $force_aspect_ratio 
		 * @return void
		 */
		public function setVideoDimensions($width, $height=null, $auto_adjust_dimensions_to_optimal=true, $force_aspect_ratio=false)
		{
			if($width === null)
			{
				$this->_format['video_dimensions'] = null;
				return $this;
			}
			
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
			
			if(empty($width) === true || $width <= 0)
			{
				throw new Exception('Unrecognised width dimension "'.$width.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoDimensions');
			}
			if(empty($height) === true || $height <= 0)
			{
				throw new Exception('Unrecognised height dimension "'.$height.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoDimensions');
			}
			
			$this->_format['video_dimensions'] = array(
				'width' => $width,
				'height' => $height,
				'auto_adjust_dimensions' => $auto_adjust_dimensions_to_optimal,
				'force_aspect' => $force_aspect_ratio,
			);
			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $width 
		 * @param string $height 
		 * @return void
		 */
		public function setVideoScale($width, $height)
		{
			$this->_blockSetOnInputFormat('video scale');
			
			if($width === null)
			{
				$this->_format['video_scale'] = null;
				return $this;
			}
			
			if($width <= 0)
			{
				throw new Exception('Unrecognised width dimension "'.$width.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoScale');
			}
			if($height <= 0)
			{
				throw new Exception('Unrecognised height dimension "'.$height.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoScale');
			}
			
			$this->_format['video_scale'] = array(
				'width' => $width,
				'height' => $height,
			);
			return $this;
		}
		
		// ERROR(s) 
		// Input area 0:8:176:152 not within the padded area 0:0:192:144 or zero-sized
		/**
		 * WARNING! if you are segmenting or spliting the file, adding padding can and will take an extrordinaty amount
		 * of time. You would be better of first segmenting/spliting to new files and then add padding to each segment.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $top 
		 * @param string $right 
		 * @param string $bottom 
		 * @param string $left 
		 * @param string $width 
		 * @param string $height 
		 * @param string $colour 
		 * @return void
		 */
		public function setVideoPadding($top, $right, $bottom, $left, $width=null, $height=null, $colour='black')
		{
			if($top === null)
			{
				$this->_format['video_padding'] = null;
				return $this;
			}
			
//			if width or heights haven't been supplied then...
			if($width === null || $height === null)
			{
//				..either get the dimension data from the format option
				if(empty($this->_format['video_dimensions']) === false)
				{
					$width = $width === null ? $this->_format['video_dimensions']['width'] : $width;
					$height = $height === null ? $this->_format['video_dimensions']['height'] : $height;
				}
//				...or if the media object has been set get the dimensions of the media object.
				else if(empty($this->_media_object) === false)
				{
					$dimensions = $this->_media_object->readDimensions();
					if(empty($dimensions) === false)
					{
						$width = $width === null ? $dimensions['width'] : $width;
						$height = $height === null ? $dimensions['height'] : $height;
					}
				}
			}
			
			$this->_format['video_padding'] = array(
				'_padding' => array(
					'top' => $top,
					'right' => $right,
					'bottom' => $bottom,
					'left' => $left,
				),
				'width' => $width === null ? null : $width+$right+$left,
				'height' => $height === null ? null : $height+$top+$bottom,
				'x' => $left,
				'y' => $top,
				'colour' => $colour,
			);
			
//			if width and height is set then we must use the scale filter instead of dimensions
			if($width !== null && $height !== null)
			{
				$this->setVideoDimensions(null);
				$this->setVideoScale($width, $height);
			}
			
			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $aspect_ratio 
		 * @param string $auto_adjust_dimensions 
		 * @return void
		 */
		public function setVideoAspectRatio($aspect_ratio, $auto_adjust_dimensions=false)
		{
			$this->_blockSetOnInputFormat('video aspect ratio');
			
			if($aspect_ratio === null)
			{
				$this->_format['video_aspect_ratio'] = null;
				return $this;
			}
			
		    if(preg_match('/^[0-9]+.[0-9]+$/', $aspect_ratio) === 0 && preg_match('/^[0-9]+:[0-9]+$/', $aspect_ratio) === 0)
			{
				throw new Exception('Unrecognised aspect ratio "'.$aspect_ratio.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoAspectRatio');
		    }
			
			$this->_format['video_aspect_ratio'] = array(
				'ratio' => $aspect_ratio,
				'auto_adjust_dimensions' => $auto_adjust_dimensions,
			);
			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $frame_rate 
		 * @return void
		 */
		public function setVideoFrameRate($frame_rate)
		{
			// PEG1/2 does not support 5/1 fps
			if($frame_rate === null)
			{
				$this->_format['video_frame_rate'] = null;
				return $this;
			}
			
			if($frame_rate < 1)
			{
				throw new Exception('Unrecognised frame rate "'.$frame_rate.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoFrameRate');
			}
			else if(is_int($frame_rate) === false && is_float($frame_rate) === false)
			{
				throw new Exception('If setting frame rate please make sure it is either an integer or a float.');
			}
			
//			now check the class settings to see if restricted codecs have been set and have to be obeys
			if($this->_restricted_video_frame_rates !== null)
			{
				if(in_array($frame_rate, $this->_restricted_video_frame_rates) === false)
				{
					throw new Exception('The frame rate "'.$frame_rate.'" cannot be set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoFrameRate. Please select one of the following frame rates: '.implode(', ', $this->_restricted_video_frame_rates));
				}
			}
			
			$this->_format['video_frame_rate'] = $frame_rate;
			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $max_frame_count 
		 * @return void
		 */
		public function setVideoMaxFrames($max_frame_count)
		{
			// PEG1/2 does not support 5/1 fps
			if($max_frame_count === null)
			{
				$this->_format['video_max_frames'] = null;
				return $this;
			}
			
			if($max_frame_count < 1)
			{
				throw new Exception('Unrecognised max frame count "'.$max_frame_count.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoFrameRate');
			}
			
			$this->_format['video_max_frames'] = $max_frame_count;
			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $bitrate 
		 * @return void
		 */
		public function setVideoBitrate($bitrate)
		{
			if($bitrate === null)
			{
				$this->_format['video_bitrate'] = null;
				return $this;
			}
			
//			expand out any short hand
			if(preg_match('/^[0-9]+k$/', $bitrate) > 0)
			{
				// TODO make this exapnd out the kbs values
			}
			
//			now check the class settings to see if restricted codecs have been set and have to be obeys
			if($this->_restricted_video_bitrates !== null)
			{
				if(in_array($bitrate, $this->_restricted_video_bitrates) === false)
				{
					throw new Exception('The bitrate "'.$bitrate.'" cannot be set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoBitrate. Please select one of the following bitrates: '.implode(', ', $this->_restricted_video_bitrates));
				}
			}
			
			$this->_format['video_bitrate'] = $bitrate;
			return $this;
			
			//throw new Exception('Unrecognised video bitrate "'.$bitrate.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoBitrate');
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $pixel_format 
		 * @return void
		 */
		public function setVideoPixelFormat($pixel_format)
		{
			if($pixel_format === null)
			{
				$this->_format['video_pixel_format'] = null;
				return $this;
			}
			
//			now check the class settings to see if restricted pixel formats have been set and have to be obeyed
			if($this->_restricted_video_pixel_formats !== null)
			{
				if(in_array($video_codec, $this->_restricted_video_pixel_formats) === false)
				{
					throw new Exception('The video pixel format "'.$pixel_format.'" cannot be set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoPixelFormat. Please select one of the following pixel formats: '.implode(', ', $this->_restricted_video_pixel_formats));
				}
			}
			
			$valid_pixel_formats = $this->getPixelFormats();
			if(in_array($pixel_format, array_keys($valid_pixel_formats)) === true)
			{
				$this->_format['video_pixel_format'] = $pixel_format;
				return $this;
			}
			
			throw new Exception('Unrecognised pixel format "'.$pixel_format.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoPixelFormat');
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $quality 
		 * @return void
		 */
		public function setVideoQuality($quality)
		{
			$this->_blockSetOnInputFormat('video quality');
			
			if($quality === null)
			{
				$this->_format['video_quality'] = null;
				return $this;
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
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $rotation 
		 * @return void
		 */
		public function setVideoRotation($rotation)
		{
			$this->_blockSetOnInputFormat('video rotation');
			
			if(in_array('transpose', $this->getFilters()) === false)
			{
				throw new Exception('Unable to rotate the video as your version of ffmpeg does not support the transpose video filter.');
			}
			
			if($rotation === null)
			{
				$this->_format['video_rotation'] = null;
				return $this;
			}
			
//			if true is set we will attmempt to auto rotate the video according to meta data.
//			the meta data will then be removed from the outputted video.
			if($rotation === true)
			{
				$this->_format['video_rotation'] = true;
				return $this;
			}
			
//			otherwise accept only the following integers.
//			90 is the same as -270, etc.
			if(in_array($rotation, array(0, 90, 180, 270, -90, -270, -180)) === false)
			{
				throw new Exception('Unrecognised rotation "'.$rotation.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVideoRotation');
			}
			
//			get the transpose code. Note that we can't transpose 180 degrees, for that we must perform flips
			$transpose = $rotation === 90 || $rotation === -270 ? 1 : ($rotation === 270 || $rotation === -90 ? 2 : 'flip');
			if($transpose === 'flip')
			{
				$this->_format['video_rotation'] = null;
				$this->videoFlipVertical();
				$this->videoFlipHorizontal();
			}
			else
			{
				$this->_format['video_rotation'] = $transpose;
			}
			
			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function videoFlipVertical()
		{
			$this->_blockSetOnInputFormat('video rotation');
			
			if(empty($this->_format['video_flip_vertical']) === false)
			{
				$this->_format['video_flip_vertical'] = null;
			}
			else
			{
				$this->_format['video_flip_vertical'] = true;
			}
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function videoFlipHorizontal()
		{
			$this->_blockSetOnInputFormat('video rotation');
			
			if(empty($this->_format['video_flip_horizontal']) === false)
			{
				$this->_format['video_flip_horizontal'] = null;
			}
			else
			{
				$this->_format['video_flip_horizontal'] = true;
			}
		}
		
	}
