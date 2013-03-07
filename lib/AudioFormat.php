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
		protected $_restricted_audio_bitrates;
		protected $_restricted_audio_sample_frequencies;
		protected $_restricted_audio_codecs;

		public function __construct($input_output_type, Config $config=null)
		{
			parent::__construct($input_output_type, $config);
			
			$this->_format = array_merge($this->_format, array(
				'disable_audio' => false,
				'audio_quality' => null,
				'audio_codec' => null,
				'audio_bitrate' => null,
				'audio_sample_frequency' => null,
				'audio_channels' => null,
				'audio_volume' => null,
				'audio_filters' => null,
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
			
			$this->_restricted_audio_bitrates = null;
			$this->_restricted_audio_sample_frequencies = null;
			$this->_restricted_audio_codecs = null;
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
			
			// TODO expand the video_filters format data
			if(empty($this->_format['audio_filters']) === false)
			{
				
			}

			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param AudioFilter $filter 
		 * @return void
		 */
		public function addAudioFilter(AudioFilter $filter)
		{
			$this->_blockSetOnInputFormat('audio filter');
			
			$this->_setFilter('audio_filters', $filter);
			
			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function disableAudio()
		{
			if($this->_type === 'input')
			{
				throw new Exception('Audio cannot be disabled on an input '.get_class($this).'.');
			}
			
			$this->_format['disable_audio'] = true;
			
			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function enableAudio()
		{
			$this->_format['disable_audio'] = false;
			
			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $audio_codec 
		 * @return void
		 */
		public function setAudioCodec($audio_codec)
		{
			if($audio_codec === null)
			{
				$this->_format['audio_codec'] = null;
				return $this;
			}
			
//			validate the audio codecs that are available from ffmpeg.
			$codecs = array_keys($this->getCodecs('audio'));
//			special case for copy as it is not included in the codec list but is valid
			array_push($codecs, 'copy');
			
// 			run a libmp3lame check as it require different mp3 codec
// 			updated. thanks to Varon for providing the research
			if(in_array($audio_codec, array('mp3', 'libmp3lame')) === true)
			{
				$audio_codec = isset($codecs['libmp3lame']) === true ? 'libmp3lame' : 'mp3';
			}
//			fix vorbis
			else if($audio_codec === 'vorbis' || $audio_codec === 'libvorbis' )
			{
				$audio_codec = isset($codecs['libvorbis']) === true ? 'libvorbis' : 'vorbis';
			}
			
			if(in_array($audio_codec, $codecs) === false)
			{
				throw new Exception('Unrecognised audio codec "'.$audio_codec.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioCodec');
			}
			
//			now check the class settings to see if restricted pixel formats have been set and have to be obeyed
			if($this->_restricted_audio_codecs !== null)
			{
				if(in_array($audio_codec, $this->_restricted_audio_codecs) === false)
				{
					throw new Exception('The audio codec "'.$audio_codec.'" cannot be set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioCodec. Please select one of the following codecs: '.implode(', ', $this->_restricted_audio_codecs));
				}
			}
			
			$this->_format['audio_codec'] = $audio_codec;
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
		public function setAudioBitrate($bitrate)
		{
			$this->_blockSetOnInputFormat('audio bitrate');
			
			if($bitrate === null)
			{
				$this->_format['audio_bitrate'] = null;
				return $this;
			}
			
//			expand out any short hand
			if(preg_match('/^[0-9]+k$/', $bitrate) > 0)
			{
				// TODO make this exapnd out the kbs values
			}
			
//			now check the class settings to see if restricted audio bitrates have been set and have to be obeys
			if($this->_restricted_audio_bitrates !== null)
			{
				if(in_array($bitrate, $this->_restricted_audio_bitrates) === false)
				{
					throw new Exception('The bitrate "'.$bitrate.'" cannot be set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioBitrate. Please select one of the following bitrates: '.implode(', ', $this->_restricted_audio_bitrates));
				}
			}
			
			$this->_format['audio_bitrate'] = $bitrate;
			return $this;
			
			//throw new Exception('Unrecognised audio bitrate "'.$bitrate.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioBitrate');
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $audio_sample_frequency 
		 * @return void
		 */
		public function setAudioSampleFrequency($audio_sample_frequency)
		{
			if($audio_sample_frequency === null)
			{
				$this->_format['audio_sample_frequency'] = null;
				return $this;
			}
			
			$audio_sample_frequency = (int) $audio_sample_frequency;
		    if($audio_sample_frequency <= 0)
			{
				throw new Exception('Unrecognised audio sample frequency "'.$format.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioSampleFrequency');
		    }
			
//			now check the class settings to see if restricted audio audio sample frequencies have been set and have to be obeyed
			if($this->_restricted_audio_sample_frequencies !== null)
			{
				if(in_array($audio_sample_frequency, $this->_restricted_audio_sample_frequencies) === false)
				{
					throw new Exception('The audio sample frequency "'.$audio_sample_frequency.'" cannot be set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioSampleFrequency. Please select one of the following sample frequencies: '.implode(', ', $this->_restricted_audio_sample_frequencies));
				}
			}
				
			$this->_format['audio_sample_frequency'] = $audio_sample_frequency;
			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $channels 
		 * @return void
		 */
		public function setAudioChannels($channels)
		{
			if($channels === null)
			{
				$this->_format['audio_channels'] = null;
				return $this;
			}
			
			if(in_array($channels, array(0, 1, 2, 6)) === false)
			{
				$this->_format['audio_channels'] = $channels;
				return $this;
			}
			
			throw new Exception('Unrecognised audio channels "'.$channels.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioChannels');
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $volume 
		 * @return void
		 */
		public function setVolume($volume)
		{
			if($volume === null)
			{
				$this->_format['audio_volume'] = null;
				return $this;
			}
			
			if($volume < 0)
			{
				throw new Exception('Unrecognised volume value "'.$volume.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVolume');
			}
			
			$this->_format['audio_volume'] = $volume;
			return $this;
		}
		
		/**
		 * undocumented function
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $quality 
		 * @return void
		 */
		public function setAudioQuality($quality)
		{
			$this->_blockSetOnInputFormat('audio quality');
			
			if($quality === null)
			{
				$this->_format['audio_quality'] = null;
				return $this;
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
