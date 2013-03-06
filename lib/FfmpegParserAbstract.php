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
	abstract class FfmpegParserAbstract extends Parser
	{
		/**
		 * Returns the raw codec data.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return string
		 */
		abstract function getRawCodecData($read_from_cache=true);
		
		/**
		 * Returns the raw filters data.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return string
		 */
		abstract function getRawBitstreamFiltersData($read_from_cache=true);
		
		/**
		 * Returns the raw filters data.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return string
		 */
		abstract function getRawFiltersData($read_from_cache=true);
		
		/**
		 * Returns the raw protocols data.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return string
		 */
		abstract function getRawProtocolsData($read_from_cache=true);
		
		/**
		 * Returns the raw data returned from ffmpeg about the available supported pixel formats.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return string
		 */
		public function getRawPixelFormatsData($read_from_cache=true)
		{
			$cache_key = 'ffmpeg_parser/raw_pixel_formats_data';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
			{
				return $data;
			}
			
			$exec = new FfmpegProcess('ffmpeg', $this->_config);
			$data = $exec->addCommand('-pix_fmt', 'list')
				 		 ->addCommand('-pix_fmts')
						 ->execute()
						 ->getBuffer();
			
//			check the process for any errors.
			if($exec->hasError() === true)
			{
				throw new FfmpegProcessException('An error was encountered when attempting to read the pixel format data. FFmpeg reported: '.$exec->getLastLine(), null, $exec);
			}
			
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns the raw data returned from ffmpeg help command.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return string
		 */
		public function getRawCommandsData($read_from_cache=true)
		{
			$cache_key = 'ffmpeg_parser/raw_commands_data';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
			{
				return $data;
			}
			
			$exec = new FfmpegProcess('ffmpeg', $this->_config);
			$data = $exec->addCommand('-h', 'long')
						 ->execute()
						 ->getBuffer();
			
//			check the process for any errors.
			if($exec->hasError() === true)
			{
				throw new FfmpegProcessException('An error was encountered when attempting to read FFmpegs\' available commands. FFmpeg reported: '.$exec->getLastLine(), null, $exec);
			}
			
			$this->_cacheSet($cache_key, $data);
			return $data;
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
			$cache_key = 'ffmpeg_parser/ffmpeg_data';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
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
				$data['binary']['vhook-support'] = in_array('--enable-vhook', $config_flags[0]) === true || in_array('--disable-vhook', $config_flags[0]) === false;

				if(preg_match('/built on (.*)(?:, gcc:| with) (.*)/', $raw_data, $conf) > 0) // 
				{
					$data['compiler']['gcc'] = $conf[2];
					//$data['compiler']['build_date'] = $conf[1];
					$data['compiler']['build_date'] = strtotime($conf[1]);
				}
			}
			
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns the version of ffmpeg if it is found and available.
		 *
		 * @access public
		 * @author Jorrit Schippers
		 * @param boolean $read_from_cache 
		 * @return void
		 */
		public function getVersion($read_from_cache=true)
		{
			$cache_key = 'ffmpeg_parser/ffmpeg_version';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
			{
				return $data;
			}
			
			$version = null;
			$build = null;
			
			$raw_data = $this->getRawFfmpegData($read_from_cache);
			
// 			Search for SVN string
// 			FFmpeg version SVN-r20438, Copyright (c) 2000-2009 Fabrice Bellard, et al.
			if($build === null && preg_match('/(?:ffmpeg|avconv) version SVN-r([0-9.]*)/i', $raw_data, $matches) > 0)
			{
				$build = $matches[1];
			}

// // 			Some OSX versions are built from a very early CVS
// // 			I do not know what to do with this version- using 1 for now
// 			if(preg_match('/(?:ffmpeg|avconv) version(.*)CVS.*Mac OSX universal/msUi', $raw_data, $matches) > 0)
// 			{
// 				$build = $matches[1];
// 			}

// 			Search for git string
// 			FFmpeg version git-N-29240-gefb5fa7, Copyright (c) 2000-2011 the FFmpeg developers.
// 			ffmpeg version N-31145-g59bd0fe, Copyright (c) 2000-2011 the FFmpeg developers
			if($build === null && preg_match('/(?:ffmpeg|avconv) version.*N-([0-9.]*)/i', $raw_data, $matches) > 0)
			{
// 				Versions above this seem to be ok
				if($matches[1] >= 29240)
				{
					$build = $matches[1];
				}
			}

// 			Do we have a release?
// 			ffmpeg version 0.4.9-pre1, build 4736, Copyright (c) 2000-2004 Fabrice Bellard
			if($build === null && preg_match('/(?:ffmpeg|avconv) version ([^,]+) build ([0-9]+),/i', $raw_data, $matches) > 0)
			{
				$version = $matches[1];
				$build = $matches[2];
			}

// 			Do we have a build version?
// 			ffmpeg version 0.4.9-pre1, build 4736, Copyright (c) 2000-2004 Fabrice Bellard
			if($build === null && preg_match('/(?:ffmpeg|avconv) version.*, build ([0-9]*)/i', $raw_data, $matches) > 0)
			{
				$build = $matches[1];
			}
			
//			ffmpeg version 1.1.2 Copyright (c) 2000-2013 the FFmpeg developers
			if($version === null && preg_match('/ffmpeg version ([^\s]+) Copyright/i', $raw_data, $matches) > 0)
			{
				$version = $matches[1];
			}
			
//			get the version from -version
			if($version === null)
			{
				$exec = new FfmpegProcess('ffmpeg', $this->_config);
				$data = $exec->addCommand('-version')
					 		 ->execute()
							 ->getBuffer();
			
//				check the process for any errors.
				if($exec->hasError() === true)
				{
					throw new FfmpegProcessException('An error was encountered when attempting to read FFmpegs\' version. FFmpeg reported: '.$exec->getLastLine(), null, $exec);
				}
				
				if(preg_match('/FFmpeg version ([0-9\.]+)/i', $data, $matches) > 0)
				{
					$version = $matches[1];
				}
				else if(preg_match('/FFmpeg ([0-9]+\.[0-9]+\.[0-9]+)/i', $data, $matches) > 0)
				{
					$version = $matches[1];
				}
			}
			
//			if both version and build are not available throw a new exception to get the user to provide their ffmpeg data to github so we can start building up different formats of ffmpeg output.
			if($version === null && $build === null)
			{
				throw new Exception('Unable to determine your FFmpeg version or build. Please create an issue at the github repository for PHPVideoToolkit 2; https://github.com/buggedcom/phpvideotoolkit-v2/issues. Please add the following data to the ticket:<br />
<br />				
<code>'.$this->getRawFfmpegData($read_from_cache).'</code>');
			}
			
			$data = array(
				'build' => $build,
				'version' => $version, 
			);
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns the information about ffmpeg itself.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getInformation($read_from_cache=true)
		{
			$cache_key = 'ffmpeg_parser/parsed_information';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
			{
				return $data;
			}
			
			$data 							= $this->getFfmpegData($read_from_cache);
			$data['version'] 				= $this->getVersion($read_from_cache);
			$data['has-ffmpeg-php-support'] = $this->hasFfmpegPhpSupport($read_from_cache);
			$data['formats'] 				= $this->getFormats($read_from_cache);
			$data['codecs'] 				= $this->getCodecs(null, $read_from_cache);
			$data['protocols'] 				= $this->getProtocols($read_from_cache);
			$data['pixel_formats'] 			= $this->getPixelFormats($read_from_cache);
			//$data['commands'] 				= $this->getCommands();
			
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Determines if we are able to utilise FFmpeg-PHP either through a loaded
		 * module or one of our emulators.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return mixed Returns false (boolean) on failure otherwise returns module or emulated.
		 */
		public function hasFfmpegPhpSupport($read_from_cache=true)
		{
			$cache_key = 'ffmpeg_parser/ffmpeg_php_available';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key, -1)) !== -1)
			{
				return $data;
			}
			
//			check to see if the module is loaded.
			if(extension_loaded('ffmpeg') === true)
			{
				$data = 'module';
				$this->_cacheSet($cache_key, $data);
				return $data;
			}
			
//			check to see if an adapter exists
			$base_dir = dirname(dirname(__FILE__));
			if(   is_file($base_dir.'/emulators/ffmpeg-php/ffmpeg_movie.php') === true
			   && is_file($base_dir.'/emulators/ffmpeg-php/ffmpeg_frame.php') === true
			   && is_file($base_dir.'/emulators/ffmpeg-php/ffmpeg_animated_gif.php') === true)
			{
				$data = 'emulated';
				$this->_cacheSet($cache_key, $data);
				return $data;
			}
			
			$data = false;
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns the available formats in their processed easy to use format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getFormats($read_from_cache=true)
		{
			$cache_key = 'ffmpeg_parser/parsed_formats';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
			{
				return $data;
			}
			
//			get the raw format information
			$raw_data = $this->getRawFormatData();

//			then match out the relevant data, clean and process.
			$data = array();
		    if(preg_match_all('/ (DE|D|E) (.*) {1,} (.*)/', $raw_data, $format_matches) > 0)
			{
		        foreach($format_matches[0] as $key=>$match)
				{
					$format_code = strtolower(trim($format_matches[2][$key]));
					
					$mux_and_demux = $format_matches[1][$key] === 'DE';
					$data[$format_code] = array(
						'mux' 		=> $mux_and_demux === true || $format_matches[1][$key] === 'E',
						'demux' 	=> $mux_and_demux === true || $format_matches[1][$key] === 'D',
						'fullname' 	=> $format_matches[3][$key],
						'extensions'=> Formats::toExtensions($format_code),
					);
		        }
			}
			
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns the available codecs in their processed easy to use format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param mixed $component If not set or set to null then all the audio, video and subtitle codecs
		 *	are returned, otherwise if set to 'audio', 'video', or 'subtitle' then just the related data
		 * 	is returned.
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getCodecs($component=null, $read_from_cache=true)
		{
			if(in_array($component, array('video', 'audio', 'subtitle', null)) === false)
			{
				throw new Exception('Unrecognised codec component specified.');
			}
			
			$cache_key = 'ffmpeg_parser/parsed_codecs';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
			{
				return $component === null ? $data : (isset($data[$component]) === true ? $data[$component] : false);
			}
			
//			get the raw format information
			$raw_data = $this->getRawCodecData();
			
//			then match out the relevant data, clean and process.
			$data = array(
				'video' 	=> array(), 
				'audio' 	=> array(), 
				'subtitle' 	=> array()
			);
		    if(preg_match_all('/ ((?:[DEVAST ]{6})|(?:[DEVASTFB ]{8})|(?:[DEVASIL\.]{6})) ([A-Za-z0-9\_]+) (.+)/', $raw_data, $codec_matches) > 0)
			{
//				FFMPEG 0.12+
//				 D..... = Decoding supported
//				 .E.... = Encoding supported
//				 ..V... = Video codec
//				 ..A... = Audio codec
//				 ..S... = Subtitle codec
//				 ...I.. = Intra frame-only codec
//				 ....L. = Lossy compression
//				 .....S = Lossless compression
//				FFMPEG OTHER
//				 D..... = Decoding supported
//				 .E.... = Encoding supported
//				 ..V... = Video codec
//				 ..A... = Audio codec
//				 ..S... = Subtitle codec
//				 ...S.. = Supports draw_horiz_band
//				 ....D. = Supports direct rendering method 1
//				 .....T = Supports weird frame truncation
				// TODO, seperate out into distinct functions
				foreach ($codec_matches[3] as $key => $fullname)
				{
					$options = preg_split('//', $codec_matches[1][$key], -1, PREG_SPLIT_NO_EMPTY);
					if(empty($options) === false)
					{
						$id = trim($codec_matches[2][$key]);
						$type = $options[2] === 'V' ? 'video' : ($options[2] === 'A' ? 'audio' : 'subtitle');
						switch ($options[2])
						{
// 							video
							case 'V' :
								$data[$type][$id] = array(
									'encode' 					=> isset($options[1]) === true && $options[1] === 'E',
									'decode' 					=> isset($options[0]) === true && $options[0] === 'D',
									'draw_horizontal_band' 		=> isset($options[3]) === true && $options[3] === 'S',
									'direct_rendering_method_1' => isset($options[4]) === true && $options[4] === 'D',
									'weird_frame_truncation' 	=> isset($options[5]) === true && $options[5] === 'T',
									'fullname' 					=> trim($fullname),
								);
								break;
// 							audio and subtitles.
							case 'A' :
							case 'S' :
								$data[$type][$id] = array(
									'encode' 	=> isset($options[1]) === true && $options[1] === 'E',
									'decode' 	=> isset($options[0]) === true && $options[0] === 'D',
									'fullname' 	=> trim($fullname),
								);
							break;
						}
					}
				}
			}
			
			$this->_cacheSet($cache_key, $data);
			
//			are we to only return a specific component of the data?
			if($component !== null)
			{
				if(isset($data[$component]) === false)
				{
					throw new Exception('Unrecognised component "'.$component.'" specified in \\PHPVideoToolkit\\FfmpegParserAbstract::getCodecData');
				}
				
				return $data[$component];
			}
			
			return $data;
		}
		
		/**
		 * Returns the available bitstream filters in their processed easy to use format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getBitstreamFilters($read_from_cache=true)
		{
			$cache_key = 'ffmpeg_parser/parsed_bitstream_filters';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
			{
				return $data;
			}
			
//			get the raw format information
			$raw_data = $this->getRawBitstreamFiltersData();
			
//			then match out the relevant data, clean and process.
			$data = array();
		    $locate = 'Bitstream filters:';
		    if(empty($raw_data) === false && ($pos = strpos($raw_data, $locate)) !== false)
			{
				$raw_data = trim(substr($raw_data, $pos + strlen($locate)));
				$data = explode("\n", $raw_data);
				array_walk($data, function(&$filter, $key)
				{
					$filter = trim($filter);
				});
		    }
			
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns the available filters in their processed easy to use format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $just_filter_names If true then just the list of available filters will be returned.
		 * 	otherwise all the available data will be returned.
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getFilters($just_filter_names=true, $read_from_cache=true)
		{
			$component = $just_filter_names === true ? 'filters' : 'verbose';
			
			$cache_key = 'ffmpeg_parser/parsed_filters';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
			{
				return $component === 'verbose' ? $data : array_keys($data);
			}
			
//			get the raw format information
			$raw_data = $this->getRawFiltersData();

//			then match out the relevant data, clean and process.
			$data = array();
		    $locate = 'Filters:';
		    if(empty($raw_data) === false && ($pos = strpos($raw_data, $locate)) !== false)
			{
				$raw_data = trim(substr($raw_data, $pos + strlen($locate)));
				if(preg_match_all('/([a-z]+)\s+([\|VA]{1,2})\->([\|VA]{1,2})\s+(.*)/i', $raw_data, $matches) > 0)
				{
					foreach ($matches[1] as $key => $filter)
					{
						$from = $matches[2][$key];
						$from_name = $from === 'V' ? 'video1' : ($from === 'VV' ? 'video2' : ($from === 'A' ? 'audio1' : ($from === 'AA' ? 'audio2' : ($from === '|' ? 'pipe' : null))));
							
						$to = $matches[2][$key];
						$to_name = $to === 'V' ? 'video1' : ($to === 'VV' ? 'video2' : ($to === 'A' ? 'audio1' : ($to === 'AA' ? 'audio2' : ($to === '|' ? 'pipe' : null))));
						
						$data[$filter] = array(
							'filter' => $filter,
							'description' => $matches[4][$key],
							'from' => $from_name,
							'to' => $to_name,
						);
					}
				}
		    }
			
			$this->_cacheSet($cache_key, $data);

			return $component === 'verbose' ? $data : array_keys($data);
		}
		
		/**
		 * Returns the available filters in their processed easy to use format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getProtocols($read_from_cache=true)
		{
			$cache_key = 'ffmpeg_parser/parsed_protocols';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
			{
				return $data;
			}
			
//			get the raw format information
			$raw_data = $this->getRawProtocolsData();
			
//			then match out the relevant data, clean and process.
			$data = array();
		    if(empty($raw_data) === false)
			{
//				check to see if we have input and output settings.
				$input_locate = 'Input:';
				if(($input_pos = strpos($raw_data, $input_locate)) !== false)
				{
//					seperate out input and output protocols
					$input_locate_length = strlen($input_locate);
					$raw_data = substr($raw_data, $input_pos+$input_locate_length);
					$raw_data = explode('Output:', $raw_data);
					
//					process the input
					$input = trim($raw_data[0]);
					$input = explode("\n", $input);
					if(empty($input) === false)
					{
						foreach($input as $protocol)
						{
							$data[$protocol] = array(
								'input' => true,
								'output' => false,
							);
						}
					}
					
//					if the output protocols are found, process them by augmenting the existing data
//					or creating a new record in the data.
					if(isset($raw_data[1]) === true && empty($raw_data[1]) === false)
					{
						$output = trim($raw_data[1]);
						$output = explode("\n", $output);
						foreach($output as $protocol)
						{
							if(isset($data[$protocol]) === true)
							{
								$data[$protocol]['output'] = true;
							}
							else
							{
								$data[$protocol] = array(
									'input' => false,
									'output' => true,
								);
							}
						}
					}
				}
				else 
				{
//					this is the older version of the protocol format.
				    $locate = 'Supported file protocols:';
					if(($pos = strpos($raw_data, $locate)) !== false)
					{
						$raw_data = trim(substr($raw_data, $pos + strlen($locate)));
						$raw_data = explode("\n", $raw_data);
						array_walk($raw_data, function(&$protocol, $key)
						{
//							both input and output is assumed to be true
							$data[$protocol] = array(
								'input' => true,
								'output' => true,
							);
						});
				    }
				}
			}
			
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns the available filters in their processed easy to use format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getPixelFormats($read_from_cache=true)
		{
			$cache_key = 'ffmpeg_parser/parsed_pixel_formats';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
			{
				return $data;
			}
			
//			get the raw format information
			$raw_data = $this->getRawPixelFormatsData();
			
//			then match out the relevant data, clean and process.
			$data = array();
		    if(strpos($raw_data, 'Pixel formats') === false)
			{
//				Format:
//				name       nb_channels depth is_alpha
//				yuv420p         3         8      n
//				yuyv422         1         8      n
				if(preg_match_all('/(\w+)\s+(\d+)\s+(\d+)\s+(y|n)/', $raw_data, $matches, PREG_SET_ORDER) > 0)
				{
					foreach($matches as $match)
					{
//						we have to assume both encode and decode are true.
						$data[$match[1]] = array(
							'encode' 	 		   => true, 
							'decode' 	 		   => true,
							'components' 		   => (int) $match[2],
							'bpp' 		 		   => (int) $match[3],
							'hardware_accelerated' => null,
							'paletted_format' 	   => null,
							'bitstream_format'     => null,
							'alpha'      		   => $match[4] === 'y',
						);
					}
				}
		    }
		    else
			{
//				Format:
//				Pixel formats:
//				I.... = Supported Input  format for conversion
//				.O... = Supported Output format for conversion
//				..H.. = Hardware accelerated format
//				...P. = Paletted format
//				....B = Bitstream format
//				FLAGS NAME            NB_COMPONENTS BITS_PER_PIXEL
//				-----
//				IO... yuv420p                3            12
				if(preg_match_all('/(I|\.)(O|\.)(H|\.)(P|\.)(B|\.)\s+(\w+)\s+(\d+)\s+(\d+)/', $raw_data, $matches, PREG_SET_ORDER) > 0)
				{
					foreach ($matches as $match)
					{
						$data[$match[6]] = array(
							'encode' 	 		   => $match[1] === 'I',
							'decode' 	 		   => $match[2] === 'O',
							'components' 		   => (int) $match[7],
							'bpp' 		 		   => (int) $match[8],
							'hardware_accelerated' => $match[3] === 'H',
							'paletted_format' 	   => $match[4] === 'P',
							'bitstream_format'     => $match[5] === 'B',
							'alpha'      		   => null,
						);
					}
				}
		    }
			
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns the available filters in their processed easy to use format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getCommands($read_from_cache=true)
		{
			$cache_key = 'ffmpeg_parser/parsed_commands';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key)))
			{
				return $data;
			}
			
//			get the raw format information
			$raw_data = $this->getRawCommandsData();
			
//			then match out the relevant data, clean and process.
			$data = array();
		    if(preg_match_all('/\n-(\w+)(.*)  (.*)/', $raw_data, $matches, PREG_SET_ORDER) > 0)
			{
				foreach ($matches as $key=>$match)
				{
					$data_type = null;
					if(preg_match_all('/(?:\s+<(int|string|binary|flags|int64|float|rational)>)/', $match[2], $data_type_matches) > 0)
					{
						$data_type = trim($data_type_matches[2]);
					}
					
					$args = trim($match[2]);
					$args = empty($args) === true ? array() : explode(' ', $args);
					
					$deprecated = strpos($match[3], 'deprecated') !== false;
					$removed = strpos($match[3], 'Removed') !== false;
					
					$data[$match[1]] = array(
						'datatype' => $data_type,
						'description' => $match[3],
						'arguments' => $args,
						'status' => $deprecated === true ? 'deprecated' : ($removed === true ? 'removed' : null),
					);
				}
			}
			
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
	}
