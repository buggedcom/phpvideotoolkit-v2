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
	 * This class provides generic data parsing for the output from FFmpeg.
	 * Parts of the code borrow heavily from Jorrit Schippers version 
	 * of PHPVideoToolkit v 0.1.9.
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @author Jorrit Schippers
	 * @package default
	 */
	class DataParserGeneric extends DataParserAbstract
	{
		/**
		 * Returns the available formats in their processed easy to use format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getFormatData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
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
					$mux_and_demux = $format_matches[1][$key] === 'DE';
					$data[strtolower(trim($format_matches[2][$key]))] = array(
						'mux' 		=> $mux_and_demux === true || $format_matches[1][$key] === 'E',
						'demux' 	=> $mux_and_demux === true || $format_matches[1][$key] === 'D',
						'fullname' 	=> $format_matches[3][$key],
					);
		        }
			}
			
			return $data;
		}
		
		/**
		 * Returns the raw data returned from ffmpeg about the available supported codecs.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return string
		 */
		public function getRawCodecData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
			{
				return $data;
			}
			
			$exec = new ExecBuffer($this->_ffmpeg_path, $this->_temp_directory);
			$exec->addCommand('-codecs');
			$data = $exec->execute();
			
			return implode("\n", $data);
		}
		
		/**
		 * Returns the available codecs in their processed easy to use format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getCodecData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
			{
				return $data;
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
			
			return $data;
		}
		
		/**
		 * Returns the raw data returned from ffmpeg about the available supported filters.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getRawFiltersData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
			{
				return $data;
			}
			
			$exec = new ExecBuffer($this->_ffmpeg_path, $this->_temp_directory);
			$exec->addCommand('-bsfs');
			$data = $exec->execute();
			
			return implode("\n", $data);
		}
		
		/**
		 * Returns the available filters in their processed easy to use format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getFiltersData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
			{
				return $data;
			}
			
//			get the raw format information
			$raw_data = $this->getRawFiltersData();
			
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
			
			return $data;
		}
		
		/**
		 * Returns the raw data returned from ffmpeg about the available supported protocols.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getRawProtocolsData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
			{
				return $data;
			}
			
			$exec = new ExecBuffer($this->_ffmpeg_path, $this->_temp_directory);
			$exec->addCommand('-protocols');
			$data = $exec->execute();
			
			return implode("\n", $data);
		}
		
		/**
		 * Returns the available filters in their processed easy to use format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getProtocolsData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
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
						array_walk($raw_data, function(&$filter, $key)
						{
//							both input and output is assumed to be true
							$data[$filter] = array(
								'input' => true,
								'output' => true,
							);
						});
				    }
				}
			}
			
			return $data;
		}
		
		/**
		 * Returns the raw data returned from ffmpeg about the available supported pixel formats.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getRawPixelFormatsData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
			{
				return $data;
			}
			
			$exec = new ExecBuffer($this->_ffmpeg_path, $this->_temp_directory);
			$exec->addCommand('-pix_fmt', 'list');
			$exec->addCommand('-pix_fmts');
			$data = $exec->execute();
			
			return implode("\n", $data);
		}
		
		/**
		 * Returns the available filters in their processed easy to use format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getPixelFormatsData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
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
						$data['pixelformats'][$match[6]] = array(
							'encode' 	 		   => $match[1] === 'I',
							'decode' 	 		   => $match[2] === 'O',
							'components' 		   => (int) $match[7],
							'bpp' 		 		   => (int) $match[8],
							'hardware_accelerated' => $match[3] === 'H',
							'paletted_format' 	   => $match[4] === 'P',
							'bitstream_format'     => $match[5] === 'B',
						);
					}
				}
		    }
			
			return $data;
		}
		
		/**
		 * Returns the raw data returned from ffmpeg help command.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getRawCommandsData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
			{
				return $data;
			}
			
			$exec = new ExecBuffer($this->_ffmpeg_path, $this->_temp_directory);
			$exec->addCommand('-h', 'long');
			$data = $exec->execute();
			
			return implode("\n", $data);
		}
		
		/**
		 * Returns the available filters in their processed easy to use format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getCommandsData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
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
					
					$data[$match[1]] = array(
						'datatype' => $data_type,
						'description' => $match[3],
						'arguments' => $args,
					);
				}
			}
			
			return $data;
		}
	}
