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
		
		protected $_programme;
		
		protected $_temp_directory;
		
		protected $_pre_input_commands;
		protected $_post_input_commands;
		protected $_input;
		
		public function __construct($programme, $temp_directory)
		{
			$this->_programme = $programme;
			// TODO check exists and throw exception
			
			$this->_temp_directory = $temp_directory;
			// TODO check writable and throw exception

			$this->_pre_input_commands = array();
			$this->_post_input_commands = array();
			$this->_input = null;
		}
		
		/**
		 * Adds a command to be bundled into command line call to be 
		 * added to the command line call before the input file is added.
		 *
		 * @access public
		 * @param string $command
		 * @param mixed $argument
		 * @return boolean
		 */
		public function setInput($input)
		{
			$this->_input = escapeshellarg($input);
			return $this;
		}

		/**
		 * Adds a command to be bundled into command line call to be 
		 * added to the command line call before the input file is added.
		 *
		 * @access public
		 * @param string $command
		 * @param mixed $argument
		 * @return boolean
		 */
		public function addPreInputCommand($command, $argument=false)
		{
			$this->_pre_input_commands[$command] = $argument === false ? false : escapeshellarg($argument);
			return $this;
		}

		/**
		 * Adds a command to be bundled into command line call to be 
		 * added to the command line call after the input file is added.
		 *
		 * @access public
		 * @param string $command
		 * @param mixed $argument
		 * @return boolean
		 */
		public function addCommand($command, $argument=false)
		{
			$this->_post_input_commands[$command] = $argument === false ? false : escapeshellarg($argument);
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
		 * Combines the commands stored into a string
		 *
		 * @access protected
		 * @return string
		 */
		protected function _combineCommands()
		{
			$command_string = '';

//			build any pre input commands
			if(empty($this->_pre_input_commands) === false)
			{
				foreach ($this->_pre_input_commands as $command=>$argument)
				{
					$command_string .= $this->_joinCommand($command, $argument).' ';
				}
			}
			
//			add in the input
			if(empty($this->_input) === false)
			{
				$command_string .= '-i '.$this->_input;
			}
			
//			build the post input commands
			if(empty($this->_post_input_commands) === false)
			{
				foreach ($this->_post_input_commands as $command=>$argument)
				{
					$command_string .= $this->_joinCommand($command, $argument).' ';
				}
			}

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

		/**
		 * Prepares the command for execution
		 *
		 * @access protected
		 * @return string
		 */
		protected function _buildExecutableCommandString()
		{
			$command_string = $this->_combineCommands();
			
	        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' || !preg_match('/\s/', $path))
	        {
	            return $this->_programme.' '.$command_string;
	        }
	        return 'start /D "'.$this->_programme.'" /B '.$command_string;
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
		public function execute()
		{
//			build the executable string.
			$executable_string = $this->_buildExecutableCommandString();
			
//			try processing the command straight into the buffer.
//			this works on some systems but not on others.   
			// TODO check this is needed. does this process it twice?
			exec($executable_string.' 2>&1', $buffer, $err); 
			if($err !== 127)
			{ 
				if(isset($buffer[0]) === false)
				{   
//					create a temp file to store the buffered read.
					$tempfile = new TempFile($this->_temp_directory);
					$output_file = $tempfile->file(null, 'txt');
					
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
			
			return $buffer;
		}

	}