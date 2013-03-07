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
	 * Aids in the building of a Process.
	 * Ensipired by the ProcessBuilder bundled with Symphony Process component.
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @package default
	 */
	class Binary
	{
	    /**
	     * The "which" command (show the full path of a command).
		 * This function heavily borrows from Pear::System::which
	     *
	     * @param string $program The command to search for
	     * @param mixed  $fallback Value to return if $program is not found
	     *
	     * @return mixed A string with the full path or false if not found
	     * @static
	     * @author Stig Bakken <ssb@php.net>
	     * @author Oliver Lillie
	     */
	    public static function locate($program)
	    {
// 			enforce API
	        if(is_string($program) === false || empty($program) === true)
			{
	            return $fallback;
	        }

// 			full path given
	        if(basename($program) !== $program)
			{
	           	$path_elements = array(dirname($program));
	            $program = basename($program);
	        }
			else
			{
// 				Honor safe mode
	            if(!ini_get('safe_mode') || !($path = ini_get('safe_mode_exec_dir')))
				{
	                $path = getenv('PATH');
	                if(!$path)
					{
	                    $path = getenv('Path'); // some OSes are just stupid enough to do this
	                }
	            }
				
//				if we have no path to guess with, throw exception.
				if(empty($path) === true)
				{
					throw new Exception('Unable to guess environment paths. Please set the absolute path to the program "'.$program.'"');
				}
				
	            $path_elements = explode(PATH_SEPARATOR, $path);
	        }

	        if(substr(PHP_OS, 0, 3) === 'WIN')
			{
				$env_pathext = getenv('PATHEXT');
	            $exe_suffixes = empty($env_pathext) === false ? explode(PATH_SEPARATOR, $env_pathext) : array('.exe','.bat','.cmd','.com');
// 				allow passing a command.exe param
	            if(strpos($program, '.') !== false)
				{
	                array_unshift($exe_suffixes, '');
	            }
	        }
			else
			{
	            $exe_suffixes = array('');
	        }
			
//			loop and fine path.
	        foreach($exe_suffixes as $suff)
			{
	            foreach($path_elements as $dir)
				{
	                $file = $dir.DIRECTORY_SEPARATOR.$program.$suff;
	                if(@is_executable($file) === true)
					{
	                    return $file;
	                }
	            }
	        }
			
			throw new Exception('Unable to locate "'.$program.'"');
	    }
	}