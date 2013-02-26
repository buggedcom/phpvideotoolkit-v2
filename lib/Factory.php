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
	class Factory
	{
		static $program = 'ffmpeg';
		static $probe_program = 'ffprobe';
		static $program_directory = '/usr/bin';
		static $temp_directory = '/tmp';
		
		public static function setDefaultVars($temp_directory='/tmp', $default_program_path='/usr/bin', $program='ffmpeg', $probe_program='ffprobe')
		{			
			$temp_directory = realpath($temp_directory);
			
			if($temp_directory === false || is_dir($temp_directory) === false)
			{
				throw new Exception('The temp directory does not exist or is not a directory.');
			}
			else if(is_readable($temp_directory) === false)
			{
				throw new Exception('The temp directory is not readable.');
			}
			else if(is_writable($temp_directory) === false)
			{
				throw new Exception('The temp directory is not writeable.');
			}
			
			$path_env = rtrim(rtrim($default_program_path, DIRECTORY_SEPARATOR).PATH_SEPARATOR.getenv('PATH'), PATH_SEPARATOR);
			putenv('PATH='.$path_env);
			if(function_exists('apache_setenv') === true)
			{
				apache_setenv('PATH', $path_env);
			}
			
			self::$program = $program;
			self::$probe_program = $probe_program;
			self::$program_directory = $default_program_path;
			self::$temp_directory = $temp_directory;
		}
		
		public static function tempFile()
		{
			return new TempFile(self::$temp_directory);
		}
		
		public static function videoFormat($input_output_type)
		{
			return new VideoFormat($input_output_type, self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory);
		}
		
		public static function audioFormat($input_output_type)
		{
			return new AudioFormat($input_output_type, self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory);
		}
		
		public static function ffmpegParser()
		{
			return new FfmpegParser(self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory);
		}
		
		public static function mediaParser()
		{
			return new MediaParser(self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory);
		}
		
		public static function mediaProbeParser()
		{
			return new MediaProbeParser(self::$program_directory.DIRECTORY_SEPARATOR.self::$probe_program, self::$temp_directory);
		}
		
		public static function media($media_file_path, Format $media_input_format=null)
		{
			return new Media($media_file_path, $media_input_format, self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory);
		}
		
		public static function video($video_file_path, VideoFormat $video_input_format=null)
		{
			return new Video($video_file_path, $video_input_format, self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory);
		}
		
		public static function frame($frame_file_path, FrameFormat $frame_input_format=null)
		{
			return new Frame($frame_file_path, $frame_input_format, self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory);
		}
		
		public static function image($image_file_path, ImageFormat $image_input_format=null)
		{
			return new Image($image_file_path, $image_input_format, self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory);
		}
		
		public static function audio($audio_file_path, AudioFormat $audio_input_format=null)
		{
			return new Audio($audio_file_path, $audio_input_format, self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory);
		}
		
		public function animatedGif($file_path=null)
		{
			return new AnimatedGif($file_path, self::$temp_directory);
		}
		
		public static function exec($program_path)
		{
			return new Process($program_path, self::$temp_directory);
		}
	}
	