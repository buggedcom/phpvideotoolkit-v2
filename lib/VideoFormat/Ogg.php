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
	class VideoFormat_Ogg extends VideoFormat
	{
		public function __construct($input_output_type, Config $config=null)
		{
			parent::__construct($input_output_type, $config);
			
			if($input_output_type === 'output')
			{
				$this->setAudioCodec('libvorbis')
					 ->setVideoCodec('libtheora')
					 ->setFormat('ogg');
			}
			
			$this->_restricted_audio_codecs = array('libvorbis', 'vorbis');
			$this->_restricted_video_codecs = array('libtheora', 'theora');
		}
	}
