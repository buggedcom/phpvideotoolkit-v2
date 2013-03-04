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
	class VideoFormat_Flv extends VideoFormat
	{
		public function __construct($input_output_type, $ffmpeg_path, $temp_directory)
		{
			parent::__construct($input_output_type, $ffmpeg_path, $temp_directory);
			
//			default by forcing the audio codec to use mp3
			if($input_output_type === 'output')
			{
				$this->setAudioCodec('mp3');
			}
			
			$this->_restricted_audio_sample_frequencies = array(44100, 22050, 11025);
		}
		
		public function updateFormatOptions()
		{
			parent::updateFormatOptions();
			
//			ffmpeg moans about audio sample frequencies on videos that aren't one of the following
//			audio sample rates. 44100, 22050, 11025
			if(empty($this->_format['audio_sample_frequency']) === true)
			{
				$audio_data = $this->_media_object->readAudioComponent();
				if(empty($audio_data['sample']['rate']) === true)
				{
					$this->setAudioSampleFrequency($this->_restricted_audio_sample_frequencies[0]);
				}
				else if(in_array($audio_data['sample']['rate'], $this->_restricted_audio_sample_frequencies) === false)
				{
					$current_sample_rate = $audio_data['sample']['rate'];
					if($current_sample_rate > $this->_restricted_audio_sample_frequencies[0])
					{
						$current_sample_rate = $this->_restricted_audio_sample_frequencies[0];
					}
					else
					{
						// TODO
						$current_sample_rate = $this->_restricted_audio_sample_frequencies[0];
					}
					$this->setAudioSampleFrequency($current_sample_rate);
				}
			}
			
			return $this;
		}
	}
