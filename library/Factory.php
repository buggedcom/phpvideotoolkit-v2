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
		static $program_directory = '/usr/bin';
		static $temp_directory = '/tmp';
		
		public static function setDefaultVars($temp_directory='/tmp', $default_program_path='/usr/bin', $program='ffmpeg')
		{
			$path_env = rtrim($default_program_path.PATH_SEPARATOR.getenv('PATH'), PATH_SEPARATOR);
			putenv('PATH='.$path_env);
			if(function_exists('apache_setenv') === true)
			{
				apache_setenv('PATH', $path_env);
			}
			
			self::$program = $program;
			self::$program_directory = $default_program_path;
			self::$temp_directory = $temp_directory;
		}
		
		public static function videoFormat($input_output_type)
		{
			return new \PHPVideoToolkit\VideoFormat(self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory, $input_output_type);
		}
		
		public static function audioFormat($input_output_type)
		{
			return new \PHPVideoToolkit\AudioFormat(self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory, $input_output_type);
		}
		
		public static function ffmpegParser()
		{
			return new \PHPVideoToolkit\FfmpegParser(self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory);
		}
		
		public static function media($file_path)
		{
			return new \PHPVideoToolkit\Media($file_path, self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory);
		}
		
		public static function video($file_path)
		{
			return new \PHPVideoToolkit\Video($file_path, self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory);
		}
		
		public static function frame($file_path)
		{
			return new \PHPVideoToolkit\Frame($file_path, self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory);
		}
		
		public static function image($file_path)
		{
			return new \PHPVideoToolkit\Image($file_path, self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory);
		}
		
		public static function audio($file_path)
		{
			return new \PHPVideoToolkit\Audio($file_path, self::$program_directory.DIRECTORY_SEPARATOR.self::$program, self::$temp_directory);
		}
	}
	