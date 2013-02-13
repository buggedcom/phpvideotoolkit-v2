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
	class Audio extends Media
	{
		public function __construct($file_path=null, $ffmpeg_path, $temp_directory)
		{
			parent::__contruct($file_path, $ffmpeg_path, $temp_directory);
			
//			validate this media file is an audio file
			$type = $this->getType();
			if($type !== 'audio')
			{
				throw new Exception('You cannot use an \\PHPVideoToolkit\\Audio for "'.$file_path.'" as the file is not an audio file. It is reported to be a '.$type);
			}
		}
		
		/**
		 * Process the output format just before the it is compiled into commands.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param Format &$output_format 
		 * @return void
		 */
		protected function _processOutputFormat(Format &$output_format)
		{
			parent::_processOutputFormat($output_format);
			
//			check for conflictions with having audio disabled.
			$options = $output_format->getFormatOptions();
			if($options['disable_audio'] === true && empty($this->_layers) === true && empty($this->_prepends) === true && empty($this->_appends) === true)
			{
				throw new Exception('Unable to process output format to send to ffmpeg as audio has been disabled and no other inputs have been found.');
			}
		}
	}
