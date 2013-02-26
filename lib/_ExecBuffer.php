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
	class ExecBuffer
	{
		static $executed_commands = array();
		
		protected $_program;
		
		protected $_temp_directory;
		
		protected $_pre_input_commands;
		protected $_post_input_commands;
		protected $_post_ouput_commands;
		protected $_input;
		protected $_output;
		protected $_non_blocking;
		protected $_progress_handler;
		protected $_detect_error;
		
		public function __construct($program, $temp_directory)
		{
			$program_path = strpos($program, DIRECTORY_SEPARATOR) === 0 ? $program : $this->_which($program);
			if($program_path === false)
			{
				throw new Exception('Unable to locate '.$program.'.');
			}
			else if(is_executable($program_path) === false)
			{
				throw new Exception($program.' is not executable.');
			}
			$this->_program = $program_path;
			
			if(is_dir($temp_directory) === false)
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
			$this->_temp_directory = $temp_directory;

			$this->_pre_input_commands = array();
			$this->_post_input_commands = array();
			$this->_post_ouput_commands = array();
			$this->_input = null;
			$this->_non_blocking = false;
			$this->_progress_handler = null;
			$this->_detect_error = false;
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
	    protected function _which($program, $fallback = false)
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
		
		/**
		 * Sets the input.
		 *
		 * @access public
		 * @param string $input
		 * @return self
		 */
		public function setInput($input)
		{
			$this->_input = $input;
			return $this;
		}

		/**
		 * Sets the output.
		 *
		 * @access public
		 * @param string $output
		 * @return self
		 */
		public function setOutput($output)
		{
			$this->_output = $output;
			return $this;
		}

		/**
		 * Sets a special command || echo "failure" toggle for detecting failures from executed commands.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $detect_error 
		 * @return void
		 */
		public function setPhpVideoToolkitDetectError($detect_error)
		{
			if(is_null($detect_error) === false && is_bool($detect_error) === false)
			{
				throw new Exception('Value must be boolean or null value.');
			}
			
			$this->_detect_error = $detect_error;
			return $this;
		}

		/**
		 * Returns the detect error status.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return boolean
		 */
		public function getPhpVideoToolkitDetectError()
		{
			return $this->_detect_error;
		}

		/**
		 * Adds a command to be bundled into command line call to be 
		 * added to the command line call before the input file is added.
		 *
		 * @access public
		 * @param string $command
		 * @param mixed $argument
		 * @return self
		 */
		public function addPreInputCommand($command, $argument=false, $allow_command_repetition=false)
		{
			$argument = $argument === false ? false : escapeshellarg($argument);
			
			if(isset($this->_pre_input_commands[$command]) === true)
			{
				if($allow_command_repetition === false)
				{
					throw new Exception('The command "'.$command.'" has already been given and it cannot be repeated. If you wish to allow a repeating command, set $allow_command_repetition to true.');
				}
				else if(is_array($this->_pre_input_commands[$command]) === false)
				{
					$this->_pre_input_commands[$command] = array($this->_pre_input_commands[$command]);
				}
				array_push($this->_pre_input_commands[$command], $argument);
			}
			else
			{
				$this->_pre_input_commands[$command] = $argument;
			}
			
			return $this;
		}

		/**
		 * Adds a command to be bundled into command line call to be 
		 * added to the command line call after the input file is added.
		 *
		 * @access public
		 * @param string $command
		 * @param mixed $argument
		 * @return self
		 */
		public function addCommand($command, $argument=false, $allow_command_repetition=false)
		{
			$argument = $argument === false ? false : escapeshellarg($argument);
			
			if(isset($this->_post_input_commands[$command]) === true)
			{
				if($allow_command_repetition === false)
				{
					throw new Exception('The command "'.$command.'" has already been given and it cannot be repeated. If you wish to allow a repeating command, set $allow_command_repetition to true.');
				}
				else if(is_array($this->_post_input_commands[$command]) === false)
				{
					$this->_post_input_commands[$command] = array($this->_post_input_commands[$command]);
				}
				array_push($this->_post_input_commands[$command], $argument);
			}
			else
			{
				$this->_post_input_commands[$command] = $argument;
			}
			
			return $this;
		}

		/**
		 * Adds a command to be bundled into command line call to be 
		 * added to the command line call after the ouput file is added.
		 *
		 * @access public
		 * @param string $command
		 * @param mixed $argument
		 * @return self
		 */
		public function addPostOutputCommand($command, $argument=false, $allow_command_repetition=false)
		{
			$argument = $argument === false ? false : escapeshellarg($argument);
			
			if(isset($this->_post_ouput_commands[$command]) === true)
			{
				if($allow_command_repetition === false)
				{
					throw new Exception('The command "'.$command.'" has already been given and it cannot be repeated. If you wish to allow a repeating command, set $allow_command_repetition to true.');
				}
				else if(is_array($this->_post_ouput_commands[$command]) === false)
				{
					$this->_post_ouput_commands[$command] = array($this->_post_ouput_commands[$command]);
				}
				array_push($this->_post_ouput_commands[$command], $argument);
			}
			else
			{
				$this->_post_ouput_commands[$command] = $argument;
			}
			
			return $this;
		}

		/**
		 * Adds multiple commands to be bundled into command line call to be 
		 * added to the command line call after the input file is added.
		 *
		 * @access public
		 * @param array $command
		 * @return self
		 */
		public function addCommands($commands)
		{
			if(is_array($commands) === false)
			{
				throw new Exception('Commands must be supplied as an array in \\PHPVideoToolkit\\ExecBuffer::addCommands');
			}
			
			if(empty($commands) === false)
			{
				foreach ($commands as $option => $argument)
				{
					$this->addCommand($option, $argument);
				}
			}

			return $this;
		}

		/**
		 * Determines if the the command exits.
		 *
		 * @access public
		 * @param string $command
		 * @return mixed boolean if failure or value if exists.
		 */
		public function hasPreInputCommand($command)
		{
			return isset($this->_pre_input_commands[$command]) === true ? ($this->_pre_input_commands[$command] === false ? true : $this->_pre_input_commands[$command]): false;
		}
		
		/**
		 * Determines if the the command exits.
		 *
		 * @access public
		 * @param string $command
		 * @return mixed boolean if failure or value if exists.
		 */
		public function hasCommand($command)
		{
			return isset($this->_post_input_commands[$command]) === true ? ($this->_post_input_commands[$command] === false ? true : $this->_post_input_commands[$command]): false;
		}
		
		/**
		 * Determines if the the command exits.
		 *
		 * @access public
		 * @param string $command
		 * @return mixed boolean if failure or value if exists.
		 */
		public function hasPostOutputCommand($command)
		{
			return isset($this->_post_ouput_commands[$command]) === true ? ($this->_post_ouput_commands[$command] === false ? true : $this->_post_ouput_commands[$command]): false;
		}
		
		protected function _combineCommandList($commands)
		{
			$command_string = '';
			
			if(empty($commands) === false)
			{
				foreach ($commands as $command=>$argument)
				{
					if(is_array($argument) === true)
					{
						foreach ($argument as $arg)
						{
							$command_string .= $this->_joinCommand($command, $arg).' ';
						}
					}
					else
					{
						$command_string .= $this->_joinCommand($command, $argument).' ';
					}
				}
			}
			
			return $command_string;
		}
		
		/**
		 * Combines the commands stored into a string
		 *
		 * @access protected
		 * @return string
		 */
		protected function _combineCommands()
		{
			$command_string = '';

//			build any pre input commands
			$command_string .= $this->_combineCommandList($this->_pre_input_commands);
			
//			add in the input
			if(empty($this->_input) === false)
			{
				$command_string .= '-i '.escapeshellarg($this->_input).' ';
			}
			
//			build the post input commands
			$command_string .= $this->_combineCommandList($this->_post_input_commands);
			
//			add in the output
			if(empty($this->_output) === false)
			{
				$command_string .= escapeshellarg($this->_output);
			}
			
//			build the post output commands
			$command_string .= $this->_combineCommandList($this->_post_output_commands);
			
//			trim off extra whitespace and return.
		    return rtrim($command_string);
		}
		
		/**
		 * Joins a command and its related argument (if any) and returns the joined string.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $command 
		 * @param mixed $argument 
		 * @return string
		 */
		protected function _joinCommand($command, $argument)
		{
			return trim($command.(empty($argument) === false ? ' '.$argument : ''));
		}

		public function setNonBlocking($non_blocking_status=true)
		{
			if(is_bool($non_blocking_status) === false)
			{
				throw new Exception('$non_blocking_status must be a boolean value.');
			}
			
			$this->_non_blocking = $non_blocking_status;
		}
		
		public function getNonBlocking()
		{
			return $this->_non_blocking;
		}
		
		/**
		 * Prepares the command for execution
		 *
		 * @access public
		 * @return string
		 */
		public function getExecutableString()
		{
//			if we have a progress handler, we may need to add commands to the exec chain.
			if($this->_progress_handler !== null)
			{
				$this->_progress_handler->setProgressExecCommands($this);
			}
			
//			now combine all the commands we have,
			$command_string = $this->_combineCommands();
	        if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' || preg_match('/\s/', $path) === 0)
	        {
	            $command_string = $this->_program.' '.$command_string;
	        }
			else
			{
		        $command_string = 'start /D "'.$this->_program.'" /B '.$command_string;
			}

//			is this to be a non blocking request?
			if($this->_non_blocking === true)
			{
				$command_string .= ' > /dev/null 2>&1';
			}
			
//			if we have a progress handler we may need to post process the command string
			if($this->_progress_handler !== null)
			{
				$this->_progress_handler->postProcessExecCommandsString($this, $command_string);
			}
//			are we detecting a failure from the executed command?
//			if we are we can only do it with a blocking execution.
			else if($this->_detect_error === true)
			{
				$command_string = '('.$command_string.' 2>&1 || echo \'phpvideotoolkit-ffmpeg-failure\')';
			}
			
			return $command_string;
		}
		
		protected function _getTemporaryOutputFile()
		{
			$tempfile = Factory::tempFile();
			return $tempfile->file(null, 'txt');
		}
		
		public function setProgressHandler(ProgressHandlerAbstract $progress_handler=null)
		{
			$this->_progress_handler = $progress_handler;
		}
		
		/**
		 * Captures the output of a call to the command line.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $command 
		 * @param string $tmp_dir 
		 * @return void
		 */
		public function execute($i=false)
		{
//			build the executable string.
			$executable_string = $this->getExecutableString();
			
//			if we have a progress handler, start it now.
			if($this->_progress_handler !== null)
			{
				$this->_progress_handler->getReadyToExecute($this, $executable_string);
			}
//			try processing the command straight into the buffer.
//			this works on some systems but not on others.   
			exec($executable_string, $buffer, $err); 

//			if we have a progress handler, start it now.
			if($this->_progress_handler !== null)
			{
				$this->_progress_handler->startHandler();
			}
			if($err !== 127)
			{ 
				if(isset($buffer[0]) === false)
				{   
//					create a temp file to store the buffered read.
					$output_file = $this->_getTemporaryOutputFile();
						
//					ouput the buffer into the temporary output file so that we can read it back into PHP.
					exec($executable_string.' &>'.$output_file, $buffer, $err);
					
// 					loop through the lines of data and collect the buffer
					if($handle = fopen($output_file, 'r'))
					{
						$buffer = array();
					    while (feof($handle) === false)
						{
					        array_push($buffer, fgets($handle, 4096));
						}
						fclose($handle);
					}
				}
			}
			else
			{
				// TODO throw exception here.
				$buffer = array();
			}
			
//			save for future reference and debugging
			self::$executed_commands[$executable_string] = $buffer;
			Trace::vars($executable_string, $buffer, $err);
			return $buffer;
		}
		
		// At least one output file must be specified
		// (Error|Permission denied|could not seek to position|Invalid pixel format|Unknown encoder|could not find codec|does not contain any stream)

	}