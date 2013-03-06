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
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @author Jorrit Schippers
	 * @package default
	 */
	class Parser extends ParserAbstract
	{
		/**
		 * Checks to see if ffmpeg is available.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return boolean
		 */
		public function isAvailable($read_from_cache=true)
		{
			$cache_key = 'ffmpeg_available';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key, -1)) !== -1)
			{
				return $data;
			}

			$raw_data = $this->getRawFfmpegData($read_from_cache);
			$data = strpos($raw_data, 'not found') === false && strpos($raw_data, 'No such file or directory') === false;
			
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns the raw data returned from ffmpeg empty function call.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return string
		 */
		public function getRawFfmpegData($read_from_cache=true)
		{
			$cache_key = 'ffmpeg_raw_data';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key, -1)) !== -1)
			{
				return $data;
			}
			
			$exec = new FfmpegProcess('ffmpeg', $this->_config);
			$data = $exec->execute()
						 ->getBuffer();
			
			// purposley no error checking here as ffmpeg gives an error with no input given.

			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
		/**
		 * Returns the raw data returned from ffmpeg about the available supported formats.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return string
		 */
		public function getRawFormatData($read_from_cache=true)
		{
			$cache_key = 'ffmpeg_raw_formats_data';
			if($read_from_cache === true && ($data = $this->_cacheGet($cache_key, -1)) !== -1)
			{
				return $data;
			}
			
			$exec = new FfmpegProcess('ffmpeg', $this->_config);
			$data = $exec->addCommand('-formats')
						 ->execute()
						 ->getBuffer();

//			check the process for any errors.
			if($exec->hasError() === true)
			{
				throw new FfmpegProcessException('An error was encountered with FFmpeg when attempting to read the formats that FFmpeg supports. FFmpeg reported: '.$exec->getLastLine(), null, $exec);
			}
			
			$this->_cacheSet($cache_key, $data);
			return $data;
		}
		
	}
