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
	interface DataParserInterface
	{
		/**
		 * Returns the information about ffmpeg itself.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return array
		 */
		public function ffmpegInformation();
		
		/**
		 * Returns the information about a specific media file.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return array
		 */
		//public function mediaInformation($media_path);
		
		//public function isFfmpegAvailable($read_from_cache=true);
		
		// public function getRawFormatData($read_from_cache=true);
		// public function getFormatData($read_from_cache=true);
		// public function getRawCodecData($read_from_cache=true);
		// public function getCodecData($read_from_cache=true);
		// public function getRawFiltersData($read_from_cache=true);
		// public function getFiltersData($read_from_cache=true);
		// public function getRawProtocolsData($read_from_cache=true);
		// public function getProtocolsData($read_from_cache=true);
		// public function getRawPixelFormatsData($read_from_cache=true);
		// public function getPixelFormatsData($read_from_cache=true);
		// public function getRawHelpData($read_from_cache=true);
		// public function getHelpData($read_from_cache=true);
		// public function getRawFfmpegData($read_from_cache=true);
		// public function getFfmpegData($read_from_cache=true);
	}

