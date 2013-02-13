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
	class AudioFormat extends Format
	{
		public function __construct($ffmpeg_path, $temp_directory, $type='output')
		{
			parent::__construct($ffmpeg_path, $temp_directory, $type);
			
			$this->_format = array_merge($this->_format, array(
				'disable_audio' => false,
				'audio_quality' => null,
				'audio_codec' => 'copy',
				'audio_bitrate' => null,
				'audio_sample_frequency' => null,
				'audio_channels' => null,
				'audio_volume' => null,
			));
			$this->_format_to_command = array_merge($this->_format_to_command, array(
				'disable_audio' 			=> '-an',
				'audio_quality' 			=> '-q:a <setting>',
				'audio_codec' 				=> '-acodec <setting>',
				'audio_bitrate' 			=> '-ab <setting>',
				'audio_sample_frequency' 	=> '-ar <setting>',
				'audio_channels' 			=> array(
					'input' => '-request_channels <setting>',
					'output' => '-ac <setting>',
				),
				'audio_volume' 				=> '-af "volume=<setting>"',
			));
		}
		
		public function disableAudio()
		{
			$this->_format['disable_audio'] = true;
		}
		
		/**
		 * cutoff
		 * xxxx
		 * xxxx
		 * xxxx
		 * xxxx
		 * xxxx
		 * xxxx
		 * xxxx
		 * xxxx
		 */
		
		public function enableAudio()
		{
			$this->_format['disable_audio'] = false;
		}
		
		public function setAudioCodec($audio_codec)
		{
//			validate the audio codecs that are available from ffmpeg.
			$codecs = array_keys($this->getCodecs('audio'));
			if(in_array($audio_codec, $codecs) === false)
			{
				throw new Exception('Unrecognised audio codec "'.$audio_codec.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioCodec');
			}
			
// 			run a libmp3lame check as it require different mp3 codec
// 			updated. thanks to Varon for providing the research
			if($audio_codec == 'mp3')
			{
				$config_data = $this->getFfmpegData();
				if(in_array('libmp3lame', $codecs) === true || in_array('--enable-libmp3lame', $config_data['binary']['configuration']) === true)
				{
					$audio_codec = 'libmp3lame';
				}
			}
			
			$this->_format['audio_codec'] = $audio_codec;
			return $this;
		}
		
		public function setAudioBitrate($bitrate)
		{
			if($this->_type === 'input')
			{
				throw new Exception('The audio bitrate cannot be set on an input '.get_class($this).'.');
			}
			
			if(preg_match('/^[0-9]+k$/', $bitrate) > 0)
			{
				$this->_format['audio_bitrate'] = $bitrate;
				return $this;
			}
			
			throw new Exception('Unrecognised audio bitrate "'.$bitrate.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioBitrate');
		}
		
		public function setAudioSampleFrequency($audio_sample_frequency)
		{
			$audio_sample_frequency = (int) $audio_sample_frequency;
		    if(in_array($audio_sample_frequency, array(11025, 22050, 44100)) === false)
			{
				$this->_format['audio_sample_frequency'] = $audio_sample_frequency;
				return $this;
		    }
			
			throw new Exception('Unrecognised audio sample frequency "'.$format.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioSampleFrequency');
		}
		
		public function setAudioChannels($channels)
		{
			if(in_array($channels, array(0, 1, 2, 6)) === false)
			{
				$this->_format['audio_channels'] = $channels;
				return $this;
			}
			
			throw new Exception('Unrecognised audio channels "'.$channels.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioChannels');
		}
		
		public function setVolume($volume)
		{
			if($volume < 0)
			{
				throw new Exception('Unrecognised volume value "'.$volume.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVolume');
			}
			
			$this->_format['audio_volume'] = $volume;
			return $this;
		}
		
		public function setAudioQuality($quality)
		{
			if($this->_type === 'input')
			{
				throw new Exception('The audio quality cannot be set on an input '.get_class($this).'.');
			}
			
// 			interpret quality into ffmpeg value
			$quality = 31 - round(($quality / 100) * 31);
			if($quality > 31 || $quality < 1)
			{
				throw new Exception('Unrecognised quality "'.$quality.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioQuality');
			}
			
			$this->_format['audio_quality'] = $quality;
			return $this;
		}
		
		
		
	}
