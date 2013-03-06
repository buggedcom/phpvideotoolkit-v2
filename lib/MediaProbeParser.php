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
	 * This class provides generic data parsing for the output from FFmpeg from specific
	 * media files. Parts of the code borrow heavily from Jorrit Schippers version of 
	 * PHPVideoToolkit v 0.1.9.
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @author Jorrit Schippers
	 * @package default
	 */
	class MediaProbeParser extends MediaParserAbstract
	{
		public function __construct(Config $config=null)
		{
			parent::__construct($config, 'probe');
		}
		
		/**
		 * Returns the information about a specific media file.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getFileInformation($file_path, $read_from_cache=true)
		{
			$cache_key = 'media_prober/'.md5(realpath($file_path)).'_parsed_information';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
			{
				return $data;
			}
			
//			get the file data
			$data = array(
				'path'  	=> $file_path,
				'type'  	=> $this->getFileType($file_path, $read_from_cache),
				'duration'  => $this->getFileDuration($file_path, $read_from_cache),
				'bitrate'   => $this->getFileBitrate($file_path, $read_from_cache),
				'start'     => $this->getFileStart($file_path, $read_from_cache),
				'video' 	=> $this->getFileVideoComponent($file_path, $read_from_cache),
				'audio' 	=> $this->getFileAudioComponent($file_path, $read_from_cache),
				'metadata' 	=> $this->getFileGlobalMetadata($file_path, $read_from_cache),
			);

// 			cache info and return
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns the files duration as a Timecode object if available otherwise returns false.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns a Timecode object if the duration is found, otherwise returns null.
		 */
		public function getFileDuration($file_path, $read_from_cache=true)
		{
			$cache_key = 'media_prober/'.md5(realpath($file_path)).'_parsed_duration';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key, -1)) !== -1)
			{
				return $data;
			}
			
//			get the raw data
			$raw_data = $this->getFileRawInformation($file_path, $read_from_cache);
			
// 			grab the duration times from all the streams and evaluate the longest.
			$data = null;
			if(preg_match_all('/duration=(.+)/', $raw_data, $matches) > 0)
			{
				$duration = null;
				foreach ($matches[1] as $key => $time)
				{
					if($duration === null || $time > $duration)
					{
						$duration = $time;
					}
				}
				$data = $duration;
			}

			$this->_cacheSet($cache_key, $data);
			return $data;
		}

		/**
		 * Returns the files duration as a Timecode object if available otherwise returns false.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns a Timecode object if the duration is found, otherwise returns null.
		 */
		public function getFileGlobalMetadata($file_path, $read_from_cache=true)
		{
			$cache_key = 'media_prober/'.md5(realpath($file_path)).'_parsed_global_meta';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key, -1)) !== -1)
			{
				return $data;
			}
			
//			get the raw data
			$format_data = $this->getFileFormat($file_path, $read_from_cache);
			
// 			grab the duration times from all the streams and evaluate the longest.
			$data = null;
			if(isset($format_data['metadata']) === true && empty($format_data['metadata']) === false)
			{
				$data = $format_data['metadata'];
			}

			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns the files bitrate if available otherwise returns -1.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns the bitrate as an integer if available otherwise returns -1.
		 */
		public function getFileBitrate($file_path, $read_from_cache=true)
		{
			$cache_key = 'media_prober/'.md5(realpath($file_path)).'_parsed_bitrate';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
			{
				return $data;
			}
			
//			get the raw data
			$video_data = $this->getFileVideoComponent($file_path, $read_from_cache);
			
			return $file_data[$file_path] = empty($video_data['bitrate']) === false ? $video_data['bitrate'] : -1;
		}
		
		/**
		 * Returns the start point of the file as a Timecode object if available, otherwise returns null.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns a Timecode object if the start point is found, otherwise returns null.
		 */
		public function getFileStart($file_path, $read_from_cache=true)
		{
			$cache_key = 'media_prober/'.md5(realpath($file_path)).'_parsed_start';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key, -1)) !== -1)
			{
				return $data;
			}
			
//			get the raw data
			$raw_data = $this->getFileRawInformation($file_path, $read_from_cache);
			
// 			grab the start times from all the streams and evaluate the earliest.
			$data = null;
			if(preg_match_all('/start_time=(.+)/', $raw_data, $matches) > 0)
			{
				$start_timecode = null;
				foreach ($matches[1] as $key => $time)
				{
					$timecode = new Timecode($value, Timecode::INPUT_FORMAT_SECONDS);
					if($start_timecode === null || $start_timecode->total_seconds > $timecode->total_seconds)
					{
						$start_timecode = $timecode;
					}
				}
				$data = $start_timecode;
			}

			$this->_cacheSet($cache_key, $data);
			return $data;
		}

		/**
		 * Returns the start point of the file as a Timecode object if available, otherwise returns null.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns a string 'audio' or 'video' if media is audio or video, otherwise returns null.
		 */
		public function getFileType($file_path, $read_from_cache=true)
		{
			$cache_key = 'media_prober/'.md5(realpath($file_path)).'_parsed_type';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key, -1)) !== -1)
			{
				return $data;
			}
			
//			get the raw data
			$raw_data = $this->getFileRawInformation($file_path, $read_from_cache);
			
// 			grab the start times from all the streams and evaluate the earliest.
			$data = null;
			if(preg_match_all('/codec_type=(audio|video)/', $raw_data, $matches) > 0)
			{
				$type = null;
				foreach ($matches[1] as $key => $codec_type)
				{
					if($type === null || $type === 'audio')
					{
						$type = $codec_type;
					}
				}
				if($type === 'video' && strpos(mime_content_type($file_path), 'image/') !== false)
				{
					$type = 'image';
				}
				$data = $type;
			}

			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns any video information about the file if available.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns an array of found data, otherwise returns null.
		 */
		public function getFileVideoComponent($file_path, $read_from_cache=true)
		{
			$cache_key = 'media_prober/'.md5(realpath($file_path)).'_parsed_video_component';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key, -1)) !== -1)
			{
				return $data;
			}
			
//			get the raw data
			$streams = $this->getFileStreams($file_path, $read_from_cache);
			
// 			match the audio stream info
			$data = null;
			if(empty($streams) === false)
			{
//				pull out the audio stream
				$data_stream = null;
				foreach ($streams as $stream)
				{
					if(isset($stream['codec_type']) === true && $stream['codec_type'] === 'video')
					{
						$data_stream = $stream;
						break;
					}
				}
				
//				process the stream data into a sane array
				if($data_stream !== null)
				{
					$data = array(
						'stream' 				=> isset($data_stream['index']) === true ? '0:'.$data_stream['index'] : null,
						'dimensions' 			=> 	array(
							'width' 				=> isset($data_stream['width']) === true ? $data_stream['width'] : null,
							'height' 				=> isset($data_stream['height']) === true ? $data_stream['height'] : null,
						),
						'bitrate' 				=> isset($data_stream['bit_rate']) === true ? $data_stream['bit_rate'] : null,
						'time_bases' 			=> isset($data_stream['time_base']) === true ? array($data_stream['time_base']) : array(),
						'frames'				=> array(
							'total'					=> isset($data_stream['nb_frames']) === true ? $data_stream['nb_frames'] : null,
							'rate'					=> isset($data_stream['r_frame_rate']) === true ? $data_stream['r_frame_rate'] : null,
							'avg_rate'				=> isset($data_stream['avg_frame_rate']) === true ? $data_stream['avg_frame_rate'] : null,
						),
						'pixel_aspect_ratio' 	=> isset($data_stream['sample_aspect_ratio']) === true ? $data_stream['sample_aspect_ratio'] : null,
						'display_aspect_ratio' 	=> isset($data_stream['display_aspect_ratio']) === true ? $data_stream['display_aspect_ratio'] : null,
						'rotation' 				=> isset($data_stream['rotate']) === true ? $data_stream['rotate'] : null,
						'pixel_format' 			=> isset($data_stream['pix_fmt']) === true ? $data_stream['pix_fmt'] : null,
						'language' 				=> isset($data_stream['meta']) === true && isset($data_stream['meta']['language']) === true ? $data_stream['meta']['language'] : null,
						'codec' 				=> array(
							'name'					=> isset($data_stream['codec_name']) === true ? $data_stream['codec_name'] : null,
							'long_name' 			=> isset($data_stream['codec_long_name']) === true ? $data_stream['codec_long_name'] : null,
							'profile'				=> isset($data_stream['profile']) === true && $data_stream['profile'] !== 'unknown' ? $data_stream['profile'] : null,
							'tag_string'			=> isset($data_stream['codec_tag_string']) === true ? $data_stream['codec_tag_string'] : null,
							'tag'					=> isset($data_stream['codec_tag']) === true ? $data_stream['codec_tag'] : null,
							'time_base'				=> isset($data_stream['codec_time_base']) === true ? $data_stream['codec_time_base'] : null,
							'raw'					=> null,
						),
						'duration'				=> isset($data_stream['duration']) === true ? new Timecode($data_stream['duration'], Timecode::INPUT_FORMAT_SECONDS) : null,
						'start'					=> isset($data_stream['start_time']) === true ? new Timecode($data_stream['start_time'], Timecode::INPUT_FORMAT_SECONDS) : null,
						'metadata' 				=> isset($data_stream['meta']) === true ? $data_stream['meta'] : null,
						'disposition' 			=> isset($data_stream['disposition']) === true ? $data_stream['disposition'] : null,
					);
					$data['codec']['raw'] 	= trim($data['codec']['name'].' ('.trim($data['codec']['tag_string'].' / '.$data['codec']['tag'], '/').')');

					// Unused but available data within the stream
					// has_b_frames=0
					// level=31
					// timecode=N/A
					// is_avc=1
					// nal_length_size=4
					// id=N/A
					// nb_read_frames=N/A
					// nb_read_packets=N/A
				}
			}
			
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
	 	 * Returns any audio information about the file if available.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns an array of found data, otherwise returns null.
		 */
		public function getFileAudioComponent($file_path, $read_from_cache=true)
		{
			$cache_key = 'media_prober/'.md5(realpath($file_path)).'_parsed_audio_component';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key, -1)) !== -1)
			{
				return $data;
			}
			
//			get the raw data
			$streams = $this->getFileStreams($file_path, $read_from_cache);
			
// 			match the audio stream info
			$data = null;
			if(empty($streams) === false)
			{
//				pull out the audio stream
				$data_stream = null;
				foreach ($streams as $stream)
				{
					if(isset($stream['codec_type']) === true && $stream['codec_type'] === 'audio')
					{
						$data_stream = $stream;
						break;
					}
				}
				
//				process the stream data into a sane array
				if($data_stream !== null)
				{
					$data = array(
						'stream' 			=> isset($data_stream['index']) === true ? '0:'.$data_stream['index'] : null,
						'stereo' 			=> isset($data_stream['channels']) === true ? ($data_stream['channels'] == 1 ? 'mono' : ($data_stream['channels'] == 2 ? 'stereo' : '5.1')) : null,
						'channels' 			=> isset($data_stream['channels']) === true ? $data_stream['channels'] : null,
						'sample_rate' 		=> isset($data_stream['sample_rate']) === true ? $data_stream['sample_rate'] : null,
						'bitrate' 			=> isset($data_stream['bit_rate']) === true ? $data_stream['bit_rate'] : null,
						'language' 			=> isset($data_stream['meta']) === true && isset($data_stream['meta']['language']) === true ? $data_stream['meta']['language'] : null,
						'codec' 			=> array(
							'name'				=> isset($data_stream['codec_name']) === true ? $data_stream['codec_name'] : null,
							'long_name' 		=> isset($data_stream['codec_long_name']) === true ? $data_stream['codec_long_name'] : null,
							'profile'			=> isset($data_stream['profile']) === true && $data_stream['profile'] !== 'unknown' ? $data_stream['profile'] : null,
							'tag_string'		=> isset($data_stream['codec_tag_string']) === true ? $data_stream['codec_tag_string'] : null,
							'tag'				=> isset($data_stream['codec_tag']) === true ? $data_stream['codec_tag'] : null,
							'time_base'			=> isset($data_stream['codec_time_base']) === true ? $data_stream['codec_time_base'] : null,
							'raw'				=> null,
						),
						'sample' 			=> array(
							'format'			=> isset($data_stream['sample_fmt']) === true ? $data_stream['sample_fmt'] : null,
							'rate' 				=> isset($data_stream['sample_rate']) === true ? $data_stream['sample_rate'] : null,
							'bits_per' 			=> isset($data_stream['bits_per_sample']) === true ? $data_stream['bits_per_sample'] : null,
						),
						'duration'			=> isset($data_stream['duration']) === true ? new Timecode($data_stream['duration'], Timecode::INPUT_FORMAT_SECONDS) : null,
						'start'				=> isset($data_stream['start_time']) === true ? new Timecode($data_stream['start_time'], Timecode::INPUT_FORMAT_SECONDS) : null,
						'frames'			=> array(
							'total'				=> isset($data_stream['nb_frames']) === true ? $data_stream['nb_frames'] : null,
							'rate'				=> isset($data_stream['r_frame_rate']) === true ? $data_stream['r_frame_rate'] : null,
							'avg_rate'			=> isset($data_stream['avg_frame_rate']) === true ? $data_stream['avg_frame_rate'] : null,
						),
						'metadata' 			=> isset($data_stream['meta']) === true ? $data_stream['meta'] : null,
						'disposition' 		=> isset($data_stream['disposition']) === true ? $data_stream['disposition'] : null,
					);
					$data['codec']['raw'] 	= trim($data['codec']['name'].' ('.trim($data['codec']['tag_string'].' / '.$data['codec']['tag'], '/').')');
					
					// Unused but available data within the stream
					// id=N/A
					// time_base=1/48000
					// start_pts=0
					// duration_ts=989246
					// bit_rate=123939
					// nb_read_frames=N/A
					// nb_read_packets=N/A
				}
			}
			
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns a boolean value determined by the media having an audio channel.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return boolean
		 */
		public function getFileHasAudio($file_path, $read_from_cache=true)
		{
			$cache_key = 'media_prober/'.md5(realpath($file_path)).'_parsed_has_audio';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key, -1)) !== -1)
			{
				return $data;
			}
			
//			get the raw data
			$streams = $this->getFileStreams($file_path, $read_from_cache);
			
// 			match the audio stream info
			$data = false;
			if(empty($streams) === false)
			{
				foreach ($streams as $stream)
				{
					if(isset($stream['codec_type']) === true && $stream['codec_type'] === 'audio')
					{
						$data = true;
						break;
					}
				}
			}
			
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns a boolean value determined by the media having a video channel.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return boolean
		 */
		public function getFileHasVideo($file_path, $read_from_cache=true)
		{
			$cache_key = 'media_prober/'.md5(realpath($file_path)).'_parsed_has_video';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key, -1)) !== -1)
			{
				return $data;
			}
			
//			get the raw data
			$streams = $this->getFileStreams($file_path, $read_from_cache);
			
// 			match the audio stream info
			$data = false;
			if(empty($streams) === false)
			{
				foreach ($streams as $stream)
				{
					if(isset($stream['codec_type']) === true && $stream['codec_type'] === 'video')
					{
						$data = true;
						break;
					}
				}
			}
			
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns prober specific format data. The date returned is automatically
		 * split into key/value pairs, however the data should be considered "raw".
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param string $read_from_cache 
		 * @return void
		 */
		public function getFileFormat($file_path, $read_from_cache=true)
		{
			$cache_key = 'media_prober/'.md5(realpath($file_path)).'_format_data';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
			{
				return $data;
			}
			
//			get the raw data
			$raw_data = $this->getFileRawInformation($file_path, $read_from_cache);
			$raw_data = trim(substr($raw_data, strpos($raw_data, '[FORMAT]')+8, strpos($raw_data, '[/FORMAT]')));
			$raw_data = explode("\n", $raw_data);
			
//			process each line of data
			$data = array();
	        foreach($raw_data as $line)
			{
	            $chunks = explode('=', $line);
	            $key = array_shift($chunks);

	            if(empty($key) === true)
				{
	                continue;
	            }
				
				$value = trim(implode('=', $chunks));
				
				if(strpos($key, 'TAG:') === 0)
				{
					if(isset($data[$index]['metadata']) === false)
					{
						$data[$index]['metadata'] = array();
					}
					$data[$index]['metadata'][substr($key, 4)] = $value;
					continue;
				}

	            $data[$index][$key] = $value;
	        }
			
// 			cache info and return
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns prober specific streams data. The date returned is automatically
		 * split into key/value pairs, however the data should be considered "raw".
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param string $read_from_cache 
		 * @return void
		 */
		public function getFileStreams($file_path, $read_from_cache=true)
		{
			$cache_key = 'media_prober/'.md5(realpath($file_path)).'_streams_data';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
			{
				return $data;
			}
			
//			get the raw data
			$raw_data = $this->getFileRawInformation($file_path, $read_from_cache);
			$raw_data = trim(substr($raw_data, strpos($raw_data, '[STREAM]')+8, strrpos($raw_data, '[/STREAM]')));
			$raw_data = explode("\n", $raw_data);
			
//			process each line of data
			$data = array();
			$index = 0;
	        foreach($raw_data as $line)
			{
	            if($line === '[STREAM]')
				{
	                $index += 1;
	                $ret[$index] = array();
	                continue;
	            }
	            else if($line === '[/STREAM]')
				{
	                continue;
	            }

	            $chunks = explode('=', $line);
	            $key = array_shift($chunks);

	            if(empty($key) === true)
				{
	                continue;
	            }
				
				$value = trim(implode('=', $chunks));
				
				if(strpos($key, 'TAG:') === 0)
				{
					if(isset($data[$index]['metadata']) === false)
					{
						$data[$index]['metadata'] = array();
					}
					$data[$index]['metadata'][substr($key, 4)] = $value;
					continue;
				}
				else if(strpos($key, 'DISPOSITION:') === 0)
				{
					if(isset($data[$index]['disposition']) === false)
					{
						$data[$index]['disposition'] = array();
					}
					$data[$index]['disposition'][substr($key, 12)] = $value;
					continue;
				}

	            $data[$index][$key] = $value;
	        }
			
// 			cache info and return
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns the raw data provided by ffmpeg about a file.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns false if no data is returned, otherwise returns the raw data as a string.
		 */
		public function getFileRawInformation($file_path, $read_from_cache=true)
		{
//			convert to realpath
			$real_file_path = $this->_checkMediaFilePath($file_path);

			$cache_key = 'media_prober/'.md5($real_file_path).'_raw_data';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key, -1)) !== -1)
			{
				return $data;
			}

// 			execute the ffmpeg lookup
			$exec = new FfmpegProcess('ffprobe', $this->_config);
			$raw_data = $exec->setInputPath($real_file_path)
							 ->addCommand('-show_streams')
							 ->addCommand('-show_format')
							 ->execute()
							 ->getBuffer();
			
//			check the process for any errors.
			if($exec->hasError() === true)
			{
				throw new FfmpegProcessException('FFprobe encountered an error when attempting to read `'.$file_path.'`. FFprobe reported: '.$exec->getLastLine(), null, $exec);
			}
			
// 			check that some data has been obtained
			$data = array();
		    if(empty($raw_data) === true)
			{
				// TODO possible error/exception here.
		    }
			
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
	}
	
	/**
	 * TODO
	 * -sections           print sections structure and section information, and exit
	 * -show_error         show probing error
	 * -show_frames        show frames info
	 * -show_packets       show packets info
	 * -show_versions      show program and library versions
	 */
