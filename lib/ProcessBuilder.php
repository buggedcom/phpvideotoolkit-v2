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
	
	use Symfony\Component\Process;
	 
	/**
	 * Aids in the building of a Process.
	 * Ensipired by the ProcessBuilder bundled with Symphony Process component.
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @package default
	 */
	class ProcessBuilder
	{
	    protected $_binary;
	    protected $_arguments;
	    protected $_temp_directory;

	    public function __construct($binary_path='ffmpeg', $temp_directory=null)
	    {
//			if a binary path is not supplied to a lookup
			if(empty($binary_path) === true || strpos($binary_path, DIRECTORY_SEPARATOR) !== 0)
			{
				$binary_path = $this->_findBinary($binary_path);
			}
			
//			validate we have a path
			if($binary_path === false)
			{
				throw new Exception('Unable to locate '.$binary_path.'.');
			}
			else if(is_executable($binary_path) === false)
			{
				throw new Exception($binary_path.' is not executable.');
			}
	        $this->_binary_path = $binary_path;
			
			$this->_temp_directory = $temp_directory;

	        $this->_arguments = array();
	    }
		
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
	    protected function _findBinary($program, $fallback=false)
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
			
	        return $fallback;
	    }
		
	    protected function _add(&$add_to, $command, $argument=null, $allow_command_repetition=false)
	    {
			$argument = $argument === false ? false : $argument;
			
			if(isset($add_to[$command]) === true)
			{
				if($allow_command_repetition === false)
				{
					throw new Exception('The command "'.$command.'" has already been given and it cannot be repeated. If you wish to allow a repeating command, set $allow_command_repetition to true.');
				}
				else if(is_array($add_to[$command]) === false)
				{
					$add_to[$command] = array($add_to[$command]);
				}
				array_push($add_to[$command], $argument);
			}
			else
			{
				$add_to[$command] = $argument;
			}
	        return $this;
	    }
		
		public function addCommands(array $commands)
		{
			if(empty($commands) === true)
			{
				throw new Exception('Commands cannot be empty.');
			}
			
			foreach ($commands as $key => $value)
			{
				if(is_array($value) === true)
				{
					foreach ($value as $v)
					{
						$this->add($key)
							 ->add($v);
					}
				}
				else
				{
					$this->add($key);
					if($value)
					{
						$this->add($value);
					}
				}
			}
		}
		
	    public function add($command, $raw_flag=false)
	    {
			array_push($this->_arguments, $command);
			
			return $this;
	    }

	    public function setInput($stdin)
	    {
	        $this->_stdin = $stdin;

	        return $this;
	    }

		/**
		 * Combines the command List into a recognisable string.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param array $commands 
		 * @return string
		 */
		protected function _combineArgumentList($commands)
		{
			$command_string = '';
			
			if(empty($commands) === false)
			{
				foreach ($commands as $argument)
				{
					// the array ois a flag for a raw argument
					$command_string .= (is_array($argument) === true ? $argument : escapeshellarg($argument)).' ';
				}
			}
			
			return trim($command_string);
		}
		
		/**
		 * Returns the command string of the system call provided by the builder object.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getCommandString()
		{
			return $this->_binary_path.' '.$this->_combineArgumentList($this->_arguments);
		}
		
		/**
		 * Returns the main process object.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return ExecBuffer
		 */
	    public function getExecBuffer()
	    {
	        return new ExecBuffer($this->getCommandString(), $this->_temp_directory);
	    }
	}