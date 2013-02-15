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
	abstract class MediaParserAbstract extends Parser
	{
		protected function _checkMediaFilePath($file_path)
		{
//			convert to realpath
			$real_file_path = realpath($file_path);

//			validate the file exists and is readable.
			if($real_file_path === false || is_file($real_file_path) === false)
			{
				throw new Exception('The file "'.$file_path.'" cannot be found in '.get_class($this).'::_checkMediaFilePath.');
			}
			else if(is_readable($real_file_path) === false)
			{
				throw new Exception('The file "'.$file_path.'" is not readable in '.get_class($this).'::_checkMediaFilePath.');
			}
			
			return $real_file_path;
		}
		
		/**
		 * Returns the information about a specific media file.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return array
		 */
		abstract public function getFileInformation($file_path, $read_from_cache=true);
		
		/**
		 * Returns any global meta data found within the file. 
		 * NOTE: This does not return individual stream meta. That meta is returned in getFileAudioComponent
		 * and getFileVideoComponent.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns a Timecode object if the duration is found, otherwise returns null.
		 */
		abstract public function getFileGlobalMetadata($file_path, $read_from_cache=true);
		
		/**
		 * Returns the files duration as a Timecode object if available otherwise returns false.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns a Timecode object if the duration is found, otherwise returns null.
		 */
		abstract public function getFileDuration($file_path, $read_from_cache=true);
		
		/**
		 * Returns the files bitrate if available otherwise returns -1.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns the bitrate as an integer if available otherwise returns -1.
		 */
		abstract public function getFileBitrate($file_path, $read_from_cache=true);
		
		/**
		 * Returns the start point of the file as a Timecode object if available, otherwise returns null.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns a Timecode object if the start point is found, otherwise returns null.
		 */
		abstract public function getFileStart($file_path, $read_from_cache=true);
		
		/**
		 * Returns the start point of the file as a Timecode object if available, otherwise returns null.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns a string 'audio' or 'video' if media is audio or video, otherwise returns null.
		 */
		abstract public function getFileType($file_path, $read_from_cache=true);
		
		/**
		 * Returns any video information about the file if available.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns an array of found data, otherwise returns null.
		 */
		abstract public function getFileVideoComponent($file_path, $read_from_cache=true);

		/**
	 	 * Returns any audio information about the file if available.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns an array of found data, otherwise returns null.
		 */
		abstract public function getFileAudioComponent($file_path, $read_from_cache=true);
		
		/**
		 * Returns a boolean value determined by the media having an audio channel.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return boolean
		 */
		abstract public function getFileHasAudio($file_path, $read_from_cache=true);
		
		/**
		 * Returns a boolean value determined by the media having a video channel.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return boolean
		 */
		abstract public function getFileHasVideo($file_path, $read_from_cache=true);
		
		/**
		 * Returns the raw data provided by ffmpeg about a file.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param boolean $read_from_cache 
		 * @return mixed Returns false if no data is returned, otherwise returns the raw data as a string.
		 */
		abstract public function getFileRawInformation($file_path, $read_from_cache=true);
	}
