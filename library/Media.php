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
	class Media extends MediaParser
	{
		protected $_media_file_path;

		public $last_error_message;
		public $error_messages;
		
		protected $_layers;
		protected $_prepends;
		protected $_appends;
		protected $_extract_segment;
		protected $_split_options;
		
		public function __construct($file_path, $ffmpeg_path, $temp_directory)
		{
			parent::__construct($ffmpeg_path, $temp_directory);
			
			$this->last_error_message = null;
			$this->error_messages = array();
			
			$this->_layers = array();
			$this->_prepends = array();
			$this->_appends = array();
			$this->_extract_segment = null;
			$this->_split_options = null;
			
//			validate the file exists and is readable.
			if($file_path !== null)
			{
				$real_file_path = realpath($file_path);

				if(is_file($real_file_path) === false)
				{
					throw new Exception('The file "'.$file_path.'" cannot be found in \\PHPVideoToolkit\\Media::__construct.');
				}
				else if(is_readable($real_file_path) === false)
				{
					throw new Exception('The file "'.$file_path.'" is not readable in \\PHPVideoToolkit\\Media::__construct.');
				}
			
				$this->_media_file_path = $file_path;
			}
		}
		
		/**
		 * Appends a media item to the current Media object by joining the specified media
		 * object after this file, ie new Media + this Media.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param Media $media 
		 * @param Format $input_format 
		 * @return Media
		 */
		public function append(Media $media, Format $input_format=null)
		{
			return $this;
		}
		
		/**
		 * Prepends a media item to the current Media object by joining the specified media
		 * object before this file, ie this Media + new Media.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param Media $media 
		 * @param Format $input_format 
		 * @return Media
		 */
		public function prepend(Media $media, Format $input_format=null)
		{
			return $this;
		}
		
		/**
		 * Adds a media object ontop of the current
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param Media $media 
		 * @param Format $input_format 
		 * @param Timecode $timecode_start 
		 * @param Timecode $timecode_end 
		 * @param mixed $layer_index If null then the layered media object is added to the top of the list.
		 *	If an index is given then it is inserted at that index. If a layer already exists at the specified index
		 *	then the layers above and including the index are all moved up one.
		 * @return Media
		 */
		public function layer(Media $media, Format $input_format=null, Timecode $timecode_start=null, Timecode $timecode_end=null, $layer_index=null)
		{
			return $this;
		}
		
		/**
		 * Extracts a segment of the media object.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param Timecode $from_timecode 
		 * @param Timecode $to_timecode 
		 * @return Media
		 */
		public function extractSegment(Timecode $from_timecode, Timecode $to_timecode=null)
		{
			return $this;
		}
		
		/**
		 * Splits (aka ffmpeg segment) the output into multiple files.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function split($split_by, $time_delta=0, $output_list_path=null)
		{
//			check that segment is available to ffmpeg
			$ffmpeg = Factory::ffmpegParser();
			$formats = $ffmpeg->getFormats();
			if(isset($formats['segment']) === false)
			{
				throw new Exception('Unable to split media as the ffmpeg option "-segment" is not supported by your version of ffmpeg.');
			}
			
			$this->_split_options = array();
			$duration = $this->getDuration();
			
//			check the split by
			if(empty($split_by) === true)
			{
				throw new Exception('The split by value is empty, in \\PHPVideoToolkit\\'.get_class($this).'::split');
			}
//			if we have an array, it's either timecodes (seconds) or integers (frames)
			else if(is_array($split_by) === true)
			{
//				we check to see if we have a timecode object, if we do then we are spliting at exact points
				if(is_object($split_by[0]) === true)
				{
					$times = array();
					foreach ($split_by as $key=>$timecode)
					{
						if(get_class($timecode) !== 'PHPVideoToolkit\Timecode')
						{
							throw new Exception('The split by timecode specified in index '.$key.' is not a \\PHPVideoToolkit\\Timecode object.');
						}
						
//						check the timecode against the total number of seconds in the media duration.
						$seconds = $timecode->total_seconds;
						if($seconds > $duration->total_seconds)
						{
							throw new Exception('The split by timecode specified in index '.$key.' is greater than the duration of the media ('.$duration->total_seconds.' seconds).');
						}
						
						array_push($times, $seconds);
					}
				
					$this->_split_options['segment_times'] = implode(',', $times);
				}
//				otherwise we are spliting at frames
				else
				{
					$times = array();
					foreach ($split_by as $key=>$integer)
					{
						if(is_int($integer) === false)
						{
							throw new Exception('The split by frame number specified in index '.$key.' is not an integer.');
						}
						
						
//						check the frame number against the total number of frames in the media duration.
						// TODO total frame rate comparison
						// $seconds = ceil($timecode->total_seconds);
						// if($seconds > $duration->total_seconds)
						// {
						// 	throw new Exception('The split by timecode specified in index '.$key.' is greater than the duration of the media ('.$duration->total_seconds.' seconds).');
						// }
						// 
						array_push($times, $integer);
					}
				
					$this->_split_options['segment_frames'] = implode(',', $times);
				}
			}
//			anything else is treated as an integer of which each split is the same length.
			else 
			{
				if($split_by < 1)
				{
					throw new Exception('The split by value must be >= 1, in \\PHPVideoToolkit\\'.get_class($this).'::split');
				}
						
//				check the split time against the total number of seconds in the media duration.
				if($split_by > $duration->total_seconds)
				{
					throw new Exception('The split by value is greater than the duration of the media ('.$duration->total_seconds.' seconds).');
				}
						
				$this->_split_options['segment_time'] = (int) $split_by;
			}

//			check time delta
			if($time_delta < 0)
			{
				throw new Exception('The time delta specified "'.$time_delta.'", in \\PHPVideoToolkit\\'.get_class($this).'::split must be >= 0');
			}
			else if($time_delta > 0)
			{
				$this->_split_options['segment_time_delta'] = (float) $time_delta;
			}
			
//			check the directory that contains the output list is writeable
			if(empty($output_list_path) === false)
			{
				$output_list = realpath($output_list_path);
				$output_list_dir = dirname($output_list);
				if(is_dir($output_list_dir) === false)
				{
					throw new Exception('The directory for the output list file "'.$output_list_path.'" does not exist, in \\PHPVideoToolkit\\'.get_class($this).'::split');
				}
				else if(is_writeable($output_list_dir) === false)
				{
					throw new Exception('The directory for the output list file "'.$output_list_path.'" is not writeable, in \\PHPVideoToolkit\\'.get_class($this).'::split');
				}
				
				$this->_split_options['segment_list'] = $output_list_path;
			}
		}
		
		/**
		 * Saves any changes to the media file to the given save path.
		 * IMPORTANT! This save blocks PHP execution, meaning that once called, the PHP interpretter
		 * will NOT continue untill the video/audio/media file(s) have been transcoded.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param Format $output_format 
		 * @param string $save_path 
		 * @param string $overwrite 
		 * @return mixed Returns a new Media object on a successfull completion, otherwise returns false.
		 *	The last error message is set to Media->last_error_message. A full list of error messages is 
		 *	available through Media->error_messages.
		 */
		public function save(Format $output_format, $save_path, $overwrite=false)
		{
			$this->_preProcessSave($output_format, $save_path, $overwrite, $processor);
		}
		
		/**
	 	 * Saves any changes to the media file to the given save path.
	 	 * IMPORTANT! This save does NOT block PHP execution, meaning that once called, the PHP interpretter
	 	 * will IMMEDIATELY continue. PHP will continue, in all likelyhood, exit before the ffmpeg has
		 * completed the transcoding of any output.
		 *
		 * If you need to monitor the output for completion or processing then you can supplied a Processor
		 * object that will setup monitoring dependant on which processor is supplied.
	 	 *
		 * @access public
		 * @author Oliver Lillie
		 * @param Format $output_format 
		 * @param string $save_path 
		 * @param string $overwrite 
		 * @param Processor $processor
		 * @return boolean If the command is sent without error then true is returned, otherwise false.
		 *	The last error message is set to Media->last_error_message. A full list of error messages is 
		 *	available through Media->error_messages.
		 */
		public function saveNonBlocking(Format $output_format, $save_path, $overwrite=false, Processor &$processor=null)
		{
			$this->_preProcessSave($output_format, $save_path, $overwrite, $processor);
		}
		
		/**
		 * Generates the command sent through to exec to invoke ffmpeg. This can be useful if you
		 * want to manage the execution of the command yourself, for instance using a transcode queue.
		 *
		 * If you need to monitor the output for completion or processing then you can supplied a Processor
		 * object that will setup monitoring dependant on which processor is supplied.
	 	 *
		 * @access public
		 * @author Oliver Lillie
		 * @param Format $output_format 
		 * @param string $save_path 
		 * @param string $overwrite 
		 * @param Processor $processor
		 * @return string
		 */
		public function getExecutionCommand(Format $output_format, $save_path, $overwrite=false, Processor &$processor=null)
		{
			$this->_preProcessSave($output_format, $save_path, $overwrite, $processor);
		}
		
		/**
		 * All three save functions, save, saveNonBlocking and getExecutionCommand have common things they 
		 * have to do before they are processed. This function contains those execution "warm-up" procedures.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param Format $output_format 
		 * @param string $save_path 
		 * @param string $overwrite 
		 * @param Processor $processor 
		 * @return void
		 */
		protected function _preProcessSave(Format &$output_format, $save_path, $overwrite, Processor &$processor=null)
		{
//			do some pre processing of the output format
			$this->_processOutputFormat($output_format);
			
			$string = $output_format->getCommandString();
			Trace::vars($string);
			
//			check the save path.
			$basename = basename($save_path);
			$save_dir = dirname($save_path);
			$save_dir = realpath($save_dir);
			if(is_dir($save_dir) === false)
			{
				throw new Exception('The directory that the output is to be saved to, "'.$save_dir.'" does not exist.');
			}
			else if(is_writeable($save_dir) === false || is_readable($save_dir) === false)
			{
				throw new Exception('The directory that the output is to be saved to, "'.$save_dir.'" is not read-writeable.');
			}
			else if(is_file($save_dir.DIRECTORY_SEPARATOR.$basename) === true && $overwrite === false)
			{
				throw new Exception('The output file already exists and overwriting is disabled.');
			}
			else if(is_file($save_dir.DIRECTORY_SEPARATOR.$basename) === true && $overwrite === true && is_writeable($save_dir.DIRECTORY_SEPARATOR.$basename) === false)
			{
				throw new Exception('The output file already exists, overwriting is enabled however the file is not writable.');
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
//			check to see if Media->split has been called, if so process and add the commands.
			if(empty($this->_split_options) === false)
			{
//				get the options to set the split format if an output format has been set.
				$options = $output_format->getFormatOptions();
				if(empty($options['format']) === false)
				{
					$output_format->addCommand('-segment_format', $options['format']);
				}

//				we must do this via add command rather than setFormat as it rejects the segment format.
				$output_format->addCommand('-f', 'segment');
				foreach ($this->_split_options as $command => $arg)
				{
					$output_format->addCommand('-'.$command, $arg);
				}
			}
		}
		
//		The below functions override the MediaParser functions so to automatically provide
//		the media_file_path each time.
		
		/**
		 * Returns the information about a specific media file.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		public function getInformation($read_from_cache=true)
		{
			return parent::getInformation($this->_media_file_path, $read_from_cache);
		}
		
		/**
		 * Returns the files duration as a Timecode object if available otherwise returns false.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return mixed Returns a Timecode object if the duration is found, otherwise returns null.
		 */
		public function getDuration($read_from_cache=true)
		{
			return parent::getDuration($this->_media_file_path, $read_from_cache);
		}
		
		/**
		 * Returns the files bitrate if available otherwise returns -1.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return mixed Returns the bitrate as an integer if available otherwise returns -1.
		 */
		public function getBitrate($read_from_cache=true)
		{
			return parent::getBitrate($this->_media_file_path, $read_from_cache);
		}
		
		/**
		 * Returns the start point of the file as a Timecode object if available, otherwise returns null.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return mixed Returns a Timecode object if the start point is found, otherwise returns null.
		 */
		public function getStart($read_from_cache=true)
		{
			return parent::getStart($this->_media_file_path, $read_from_cache);
		}
		
		/**
		 * Returns the start point of the file as a Timecode object if available, otherwise returns null.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return mixed Returns a string 'audio' or 'video' if media is audio or video, otherwise returns null.
		 */
		public function getType($read_from_cache=true)
		{
			return parent::getType($this->_media_file_path, $read_from_cache);
		}
		
		/**
		 * Returns any video information about the file if available.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return mixed Returns an array of found data, otherwise returns null.
		 */
		public function getVideoComponent($read_from_cache=true)
		{
			return parent::getVideoComponent($this->_media_file_path, $read_from_cache);
		}
		
		/**
	 	 * Returns any audio information about the file if available.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return mixed Returns an array of found data, otherwise returns null.
		 */
		public function getAudioComponent($read_from_cache=true)
		{
			return parent::getAudioComponent($this->_media_file_path, $read_from_cache);
		}
		
		/**
		 * Returns the raw data provided by ffmpeg about a file.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return mixed Returns false if no data is returned, otherwise returns the raw data as a string.
		 */
		public function getRawInformation($read_from_cache=true)
		{
			return parent::getRawInformation($this->_media_file_path, $read_from_cache);
		}
		
		
	}
