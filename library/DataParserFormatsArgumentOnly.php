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
	class DataParserFormatsArgumentOnly extends DataParserAbstract
	{
		public function getFormatData($read_from_cache=true)
		{
			static $data = null;
			if($read_from_cache === true && empty($data) === false)
			{
				return $data;
			}
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
			static $ffmpeg_information = null;
			if($read_from_cache === true && empty($ffmpeg_information) === false)
			{
				return $ffmpeg_information;
			}
			
		}
	}