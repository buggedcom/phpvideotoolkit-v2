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
		public function __construct($audio_file_path, Config $config=null, AudioFormat $audio_input_format=null, $ensure_audio_file=true)
		{
			parent::__construct($audio_file_path, $config, $audio_input_format);
			
//			validate this media file is an audio file
			if($ensure_audio_file === true && $this->_validateMedia('audio') === false)
			{
				throw new Exception('You cannot use an instance of '.get_class($this).' for "'.$audio_file_path.'" as the file is not an audio file. It is reported to be a '.$type);
			}
		}
		
		/**
		 * Returns the default (empty) input format for the type of media object this class is.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $type Either input for an input format or output for an output format.
		 * @param string $format A specific output format (if any to use)
		 * @return Format
		 */
		public function getDefaultFormat($type, $format)
		{
			return $this->_getDefaultFormat($type, 'AudioFormat', $format);
		}
		
		protected function _savePreProcess(Format &$output_format=null, &$save_path, $overwrite, ProgressHandlerAbstract &$progress_handler=null)
		{
			parent::_savePreProcess($output_format, $save_path, $overwrite, $progress_handler);
			
//			if we are splitting the output
			if(empty($this->_split_options) === false)
			{
				$options = $output_format->getFormatOptions();
			
//				if we are splitting we need to add certain commands to make it work.
//				for video, we need to ensure that just the audio codec is set.
				if(empty($options['audio_codec']) === true)
				{
					$this->_process->addCommand('-acodec', 'copy');
				}
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
		protected function _processOutputFormat(Format &$output_format=null, &$save_path)
		{
			parent::_processOutputFormat($output_format, $save_path);
			
//			check for conflictions with having audio disabled.
			$options = $output_format->getFormatOptions();
			if($options['disable_audio'] === true && empty($this->_layers) === true && empty($this->_prepends) === true && empty($this->_appends) === true)
			{
				throw new Exception('Unable to process output format to send to ffmpeg as audio has been disabled and no other inputs have been found.');
			}
		}
	}
