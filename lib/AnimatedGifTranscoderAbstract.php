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
	abstract class AnimatedGifTranscoderAbstract
	{
		/**
		 * Adds a frame to the current timeline.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param string $frame_delay 
		 * @return boolean
		 */
		abstract public function addFrame($file_path, $frame_delay);
		
		/**
		 * Creates a new animated gif object from a selection of files.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_paths 
		 * @param string $frame_delay 
		 * @param string $loop_count 
		 * @return mixed Returns an AnimatedGif object on success, otherwise returns false.
		 */
		abstract public static function createFrom($file_paths, $frame_delay, $loop_count);
		
		/**
		 * Expands an animated gif into a list of files.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $file_path 
		 * @param string $to_path 
		 * @return mixed Returns an array of file paths on success, otherwise false.
		 */
		abstract public static function expand($file_path, $to_path);
	}
