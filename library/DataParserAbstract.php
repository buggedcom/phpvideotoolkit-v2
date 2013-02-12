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
	 * undocumented class
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @package default
	 */
	abstract class DataParserAbstract implements DataParserInterface
	{
		protected $_ffmpeg_path;
		protected $_temp_directory;
		
		public function __construct($ffmpeg_path, $temp_directory)
		{
			$this->_ffmpeg_path = $ffmpeg_path;
			// TODO validate and exceptions
			
			$this->_temp_directory = $temp_directory;
			// TODO validate and exceptions
		}
		
		/**
		 * Checks to see if ffmpeg is available.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return boolean
		 */
		public function isFfmpegAvailable($read_from_cache=true)
		{
			static $available = null;
			if($read_from_cache === true && $available !== null)
			{
				return $available;
			}
			
			$raw_data = $this->getRawFfmpegData($read_from_cache);
			$available = strpos($raw_data, 'not found') === false && strpos($raw_data, 'No such file or directory') === false;
			
			return $available;
		}
		
		/**
		 * Returns the raw data returned from ffmpeg about the available supported formats.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getRawFormatData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
			{
				return $data;
			}
			
			$exec = new ExecBuffer($this->_ffmpeg_path, $this->_temp_directory);
			$data = $exec->addCommand('-formats')
						 ->execute();
			
			return implode("\n", $data);
		}
		
		/**
		 * Returns the raw data returned from ffmpeg empty function call.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getRawFfmpegData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
			{
				return $data;
			}
			
			$exec = new ExecBuffer($this->_ffmpeg_path, $this->_temp_directory);
			$data = $exec->execute();

			return $data = implode("\n", $data);
		}
		
		/**
		 * Returns the available filters in their processed easy to use format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getFfmpegData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
			{
				return $data;
			}
			
//			get the raw format information
			$raw_data = $this->getRawFfmpegData($read_from_cache);
			
//			then match out the relevant data, clean and process.
			$data = array(
				'binary' => array(
					'configuration' => array(),
					'vhook-support' => false,
				),
				'compiler' => array(
					'gcc' => null,
					'build_date' => null,
				),
			);
			if(preg_match_all('/--[a-zA-Z0-9\-]+/', $raw_data, $config_flags) > 0)
			{
				$data['binary']['configuration'] = $config_flags[0];
				$data['binary']['vhook-support'] = in_array('--enable-vhook', $config_flags[0]) || !in_array('--disable-vhook', $config_flags[0]);

				if(preg_match('/built on (.*)(?:, gcc:| with) (.*)/', $raw_data, $conf) > 0) // 
				{
					$data['compiler']['gcc'] = $conf[2];
					//$data['compiler']['build_date'] = $conf[1];
					$data['compiler']['build_date'] = strtotime($conf[1]);
				}
			}
			
			return $data;
		}
		
		/**
		 * Returns the version of ffmpeg if it is found and available.
		 *
		 * @access public
		 * @author Jorrit Schippers
		 * @return void
		 */
		public function getVersion($read_from_cache=true)
		{
			static $version = null;
			if($version !== null)
			{
				return $version;
			}
			
			$raw_data = $this->getRawFfmpegData($read_from_cache);
			
// 			Search for SVN string
// 			FFmpeg version SVN-r20438, Copyright (c) 2000-2009 Fabrice Bellard, et al.
			if(preg_match('/(?:ffmpeg|avconv) version SVN-r([0-9.]*)/i', $raw_data, $matches) > 0)
			{
				return $version = array(
					'build' => $matches[1],
					'version' => null,
				);
			}

// 			Some OSX versions are built from a very early CVS
// 			I do not know what to do with this version- using 1 for now
			if(preg_match('/(?:ffmpeg|avconv) version(.*)CVS.*Mac OSX universal/msUi', $raw_data, $matches) > 0)
			{
				return $version = array(
						'build' => null,
						'version' => $matches[1],
					);
			}

// 			Search for git string
// 			FFmpeg version git-N-29240-gefb5fa7, Copyright (c) 2000-2011 the FFmpeg developers.
// 			ffmpeg version N-31145-g59bd0fe, Copyright (c) 2000-2011 the FFmpeg developers
			if(preg_match('/(?:ffmpeg|avconv) version.*N-([0-9.]*)/i', $raw_data, $matches) > 0)
			{
// 				Versions above this seem to be ok
				if($matches[1] >= 29240)
				{
					return $version = array(
						'build' => (int) $matches[1],
						'version' => null, 
					);
				}
			}

// 			Do we have a release?
// 			ffmpeg version 0.4.9-pre1, build 4736, Copyright (c) 2000-2004 Fabrice Bellard
			if(preg_match('/(?:ffmpeg|avconv) version ([^,]+) build ([0-9]+),/i', $raw_data, $matches) > 0)
			{
				return $version = array(
					'build' => $matches[2],
					'version' => $matches[1], 
				);
			}

// 			Do we have a build version?
// 			ffmpeg version 0.4.9-pre1, build 4736, Copyright (c) 2000-2004 Fabrice Bellard
			if(preg_match('/(?:ffmpeg|avconv) version.*, build ([0-9]*)/i', $raw_data, $matches) > 0)
			{
				return $version = array(
					'build' => $matches[1],
					'version' => null, 
				);
			}
			
//			ffmpeg version 1.1.2 Copyright (c) 2000-2013 the FFmpeg developers
			if(preg_match('/ffmpeg version ([^\s]+) Copyright/i', $raw_data, $matches) > 0)
			{
				return $version = array(
					'build' => null,
					'version' => $matches[1], 
				);
			}

			// TODO throw new exception telling them to send the data to a github issue.

			return $version = array(
					'build' => null,
					'version' => null, 
				);
		}
		
		/**
		 * Returns the information about ffmpeg itself.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return array
		 */
		public function ffmpegInformation($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
			{
				return $data;
			}
			
			$data 							= $this->getFfmpegData();
			$data['version'] 				= $this->getVersion();
			$data['has-ffmpeg-php-support'] = $this->hasFfmpegPhpSupport();
			$data['formats'] 				= $this->getFormatData();
			$data['codecs'] 				= $this->getCodecData();
			$data['protocols'] 				= $this->getProtocolsData();
			$data['pixel_formats'] 			= $this->getPixelFormatsData();
			//$data['commands'] 				= $this->getCommandsData();
			
			return $data;
		}
		
		/**
		 * Determines if we are able to utilise FFmpeg-PHP either through a loaded
		 * module or one of our emulators.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return mixed Returns false (boolean) on failure otherwise returns module or emulated.
		 */
		public function hasFfmpegPhpSupport($read_from_cache=true)
		{
			static $available = null;
			if($read_from_cache === true && $available !== null)
			{
				return $available;
			}
			
//			check to see if the module is loaded.
			if(extension_loaded('ffmpeg') === true)
			{
				return $available = 'module';
			}
			
//			check to see if an adapter exists
			$base_dir = dirname(dirname(__FILE__));
			if(   is_file($base_dir.'/emulators/ffmpeg-php/ffmpeg_movie.php') === true
			   && is_file($base_dir.'/emulators/ffmpeg-php/ffmpeg_frame.php') === true
			   && is_file($base_dir.'/emulators/ffmpeg-php/ffmpeg_animated_gif.php') === true)
			{
				$available = 'emulated';
			}
			
			return $available = false;
		}
		
		/**
		 * Returns the information about a specific media file.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function fileInformation($file_path)
		{
//			convert to realpath
			$file_path = realpath($file_path);

			if(is_file($file_path) === false)
			{
				throw new Exception('The file cannot be found in \\PHPVideoToolkit\\DataParserAbstract::fileInformation.');
			}
			else if(is_readable($file_path) === false)
			{
				throw new Exception('The file is not readable in \\PHPVideoToolkit\\DataParserAbstract::fileInformation.');
			}
			
			static $file_info = array();
			
// 			check to see if the info has already been generated
			$hash = md5_file($file_path).'_'.filemtime($file_path);
		    if(isset($file_info[$hash]) === TRUE)
			{
		      	return $file_info[$hash];
		    }

// 			execute the ffmpeg lookup
			$exec = new ExecBuffer($this->_ffmpeg_path, $this->_temp_directory);
			$raw_data = $exec->setInput($file_path)
							 ->execute();

// 			check that some data has been obtained
			$data = array();
		    if(empty($raw_data) === true)
			{
				// TODO possible error/exception here.
				return $file_info[$hash] = false;
		    }
		    else
			{
				$raw_data = implode("\n", $raw_data);
		    }

//			process raw data, but first setup placeholders
			$data['bitrate'] = null;
			$data['duration'] = null;
			$data['start'] = null;
			$data['frame_rate'] = null;
			
// 			match the video stream info
			$data['video'] = null;
			if(preg_match('/Stream(.*): Video: (.*)/', $raw_data, $matches) > 0)
			{
				$data['video'] = array(
					'dimensions' => array(
						'width' => null,
						'height' => null,
					),
					'time_bases' => array(),
					'frame_rate' => null,
					'frame_count' => null,
					'pixel_aspect_ratio' => null,
					'display_aspect_ratio' => null,
					'pixel_format' => null,
					'codec' => null,
					'metadata' => array(),
				);

// 				get the dimension parts
				if(preg_match('/([1-9][0-9]*)x([1-9][0-9]*)/', $matches[2], $dimensions_matches) > 0)
				{
					$data['video']['dimensions'] = array(
						'width' => (float) $dimensions_matches[1],
						'height' => (float) $dimensions_matches[2],
					);
				}
				$dimension_match = $dimensions_matches[0];

// 				get the timebases
				$data['video']['time_bases'] = array();
				if(preg_match_all('/([0-9\.k]+) (fps|tbr|tbc|tbn)/', $matches[0], $timebase_matches) > 0)
				{
					foreach ($timebase_matches[2] as $key => $abrv)
					{
						$data['video']['time_bases'][$abrv] = $timebase_matches[1][$key];
					}
				}
				$timebase_match = implode(', ', $timebase_matches[0]);
			
// 				get the video frames per second
				$fps = isset($data['video']['time_bases']['fps']) === true ? $data['video']['time_bases']['fps'] : 
					  (isset($data['video']['time_bases']['tbr']) === true ? $data['video']['time_bases']['tbr'] : 
				  	   false);
				if ($fps !== false)
				{
					$data['frame_rate'] = $data['video']['frame_rate'] = (float) $fps;
					$data['video']['frame_count'] = ceil($data['duration']->seconds * $data['video']['frame_rate']);
				}

// 				get the ratios
				if(preg_match('/\[PAR|SAR ([0-9\:\.]+) DAR ([0-9\:\.]+)\]/', $matches[0], $ratio_matches) > 0)
				{
					$data['video']['pixel_aspect_ratio'] = $ratio_matches[1];
					$data['video']['display_aspect_ratio'] = $ratio_matches[2];
				}
				
// 				formats should be anything left over, let me know if anything else exists
				$parts = explode(',', $matches[2]);
				$other_parts = array($dimension_match, $timebase_match);
				$formats = array();
				foreach ($parts as $key => $part)
				{
					$part = trim($part);
					if(in_array($part, $other_parts) === false)
					{
						array_push($formats, $part);
					}
				}
				$data['video']['pixel_format'] = $formats[1];
				$data['video']['codec'] = $formats[0];
				
//				get metadata from the video input, (if any)
				$meta_data_search_from = strpos($raw_data, $matches[0]);
				$meta_data_search = trim(substr($raw_data, $meta_data_search_from+strlen($matches[0])));
				if(strpos($meta_data_search, 'Metadata:') === 0 && preg_match('/Metadata:(.*)Stream/ms', $meta_data_search, $meta_matches) > 0)
				{
					if(preg_match_all('/([a-z\_]+)\s+\: (.*)/', $meta_matches[1], $meta_matches) > 0)
					{
						foreach ($meta_matches[2] as $key => $value)
						{
							$data['video']['metadata'][$meta_matches[1][$key]] = $value;
						}
					}
				}
			}
			
// 			match the audio stream info
			$data['audio'] = null;
			if(preg_match('/Stream(.*): Audio: (.*)/', $raw_data, $matches) > 0)
			{
				$data['audio'] = array(
					'stereo' 		=> null,
					'sample_rate' 	=> null,
					'bitrate' 		=> null,
					'metadata' 		=> array(),
				);
				
				$other_parts = array();
				
// 				get the stereo value
				if(preg_match('/(stereo|mono)/i', $matches[0], $stereo_matches) > 0)
				{
					$data['audio']['stereo'] = $stereo_matches[0];
					array_push($other_parts, $stereo_matches[0]);
				}
				
// 				get the sample_rate
				if(preg_match('/([0-9]{3,6}) Hz/', $matches[0], $sample_matches) > 0)
				{
					$data['audio']['sample_rate'] = (float) $sample_matches[1];
					array_push($other_parts, $sample_matches[0]);
				}

// 				get the bit rate
				if(preg_match('/([0-9]{1,3}) kb\/s/', $matches[0], $bitrate_matches) > 0)
				{
					$data['audio']['bitrate'] = (float) $bitrate_matches[1];
					array_push($other_parts, $bitrate_matches[0]);
				}

// 				formats should be anything left over, let me know if anything else exists
				$parts = explode(',', $matches[2]);
				$formats = array();
				foreach ($parts as $key => $part)
				{
					$part = trim($part);
					if(in_array($part, $other_parts) === false)
					{
						array_push($formats, $part);
					}
				}
				$data['audio']['codec'] = $formats[0];
				
//				get metadata from the audio input, (if any)
//				however if we have a video source in the media it is outputted differently than just pure audio.
				if(empty($data['video']) === false)
				{
					$meta_data_search_from = strpos($raw_data, $matches[0]);
					$meta_data_search = trim(substr($raw_data, $meta_data_search_from+strlen($matches[0])));
					if(strpos($meta_data_search, 'Metadata:') === 0 && preg_match('/Metadata:(.*)(?:Stream|At least)/ms', $meta_data_search, $meta_matches) > 0)
					{
						if(preg_match_all('/([a-z\_]+)\s+\: (.*)/', $meta_matches[1], $meta_matches) > 0)
						{
							foreach ($meta_matches[2] as $key => $value)
							{
								$data['audio']['metadata'][$meta_matches[1][$key]] = $value;
							}
						}
					}
				}
//				this is just pure audio and is essnetially id3 data.
				else if(strpos($raw_data, 'Metadata:') !== false && preg_match('/Metadata:(.*)(?:Duration)/ms', $raw_data, $meta_matches) > 0)
				{
					if(preg_match_all('/([a-z\_]+)\s+\: (.*)/', $meta_matches[1], $meta_matches) > 0)
					{
						foreach ($meta_matches[2] as $key => $value)
						{
							$data['audio']['metadata'][$meta_matches[1][$key]] = $value;
						}
					}
				}
			}
			
// 			grab the duration and bitrate data
			if(preg_match_all('/Duration: (.*)/', $raw_data, $matches) > 0)
			{
				$line = trim($matches[0][0]);
				
// 				capture any data
				preg_match_all('/(Duration|start|bitrate): ([^,]*)/', $line, $matches);
				
// 				get the data
				foreach ($matches[1] as $key => $detail)
				{
					$value = $matches[2][$key];
					switch (strtolower($detail))
					{
						case 'duration' :
							$data['duration'] = new Timecode($value, Timecode::INPUT_FORMAT_TIMECODE, '%hh:%mm:%ss.%ms');
							break;
					
						case 'bitrate' :
							$data['bitrate'] = strtoupper($value) === 'N/A' ? -1 : intval($value);
							break;
					
						case 'start' :
							$data['start'] = new Timecode($value, Timecode::INPUT_FORMAT_SECONDS);
							break;
					}
				}
			}

			$data['_raw_info'] = $raw_data;

// 			cache info and return
		    return $file_info[$hash] = $data;
		}
	}