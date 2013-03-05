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
	class VideoFormat_3gp extends VideoFormat
	{
		public function __construct($input_output_type, Config $config=null)
		{
			parent::__construct($input_output_type, $config);
			
			$this->_restricted_audio_codecs = array('aac', 'amr');
			$this->_restricted_audio_bitrates = array('4.75k', '5.15k', '5.9k', '6.7k', '7.4k', '7.95k', '8k', '10.2k', '12k', '16k', '48k', '56k', '64k', '96k', '112k', '128k', '160k', '192k', '224k', '256k', '320k');
			$this->_restricted_audio_sample_frequencies = array(8000, 11025, 12000, 16000, 22050, 24000, 32000, 44100, 48000);

			$this->_restricted_video_codecs = array('h263', 'h264', 'libx264');
			$this->_restricted_video_bitrates = array('32k', '40k', '60k', '64k', '80k', '96k', '104k', '128k', '160k', '240k', '256k');
			$this->_restricted_video_frame_rates = array(10, 12, 15, 20, 24, 25);
			
			if($input_output_type === 'output')
			{
				$this->setAudioCodec('aac')
					 ->setAudioSampleFrequency(44100)
					 ->setVideoCodec('libx264')
					 ->setFormat('3gp');
			}
		}
	}
