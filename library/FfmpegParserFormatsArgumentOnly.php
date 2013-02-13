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
	class FfmpegParserFormatsArgumentOnly extends FfmpegParserAbstract
	{
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
			return $this->getRawFormatData($read_from_cache);
		}
		
		/**
		 * Returns the raw data returned from ffmpeg about the available supported filters.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return string
		 */
		public function getRawFiltersData($read_from_cache=true)
		{
			return $this->getRawFormatData($read_from_cache);
		}
		
		/**
		 * Returns the raw data returned from ffmpeg about the available supported bitstream filters.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return string
		 */
		public function getRawBitstreamFiltersData($read_from_cache=true)
		{
			return $this->getRawFormatData($read_from_cache);
		}
		
		/**
		 * Returns the raw data returned from ffmpeg about the available supported protocols.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return string
		 */
		public function getRawProtocolsData($read_from_cache=true)
		{
			return $this->getRawFormatData($read_from_cache);
		}
	}
