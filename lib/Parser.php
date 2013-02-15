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
		 * Returns the raw data returned from ffmpeg empty function call.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return string
		 */
		public function getRawFfmpegData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
			{
				return $data;
			}
			
			$exec = new ExecBuffer($this->_program_path, $this->_temp_directory);
			$data = $exec->execute();

			return $data = implode("\n", $data);
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
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
			{
				return $data;
			}
			
			$exec = new ExecBuffer($this->_program_path, $this->_temp_directory);
			$data = $exec->addCommand('-formats')
						 ->execute();

			return $data = implode("\n", $data);
		}
		
	}
