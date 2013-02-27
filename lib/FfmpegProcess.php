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
	class FfmpegProcess extends ProcessBuilder
	{
		protected $_temp_directory;
		
		protected $_exec;
		protected $_pre_input_commands;
		protected $_post_input_commands;
		protected $_post_output_commands;
		protected $_input;
		protected $_output;
		protected $_non_blocking;
		protected $_progress_handler;
		protected $_detect_error;
		protected $_combined;
		
		public function __construct($binary_path, $temp_directory)
		{
			parent::__construct($binary_path);
			
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
			$this->_post_output_commands = array();
			$this->_input = null;
			$this->_exec = null;
			$this->_progress_handler = null;
			$this->_combined = false;
		}
		
		/**
		 * Sets the input.
		 *
		 * @access public
		 * @param string $input
		 * @return self
		 */
		public function setInputPath($input)
		{
			$this->_input = $input;
			return $this;
		}

		/**
		 * Gets the output.
		 *
		 * @access public
		 * @return string
		 */
		public function getInputPath()
		{
			return $this->_input;
		}
		
		/**
		 * Sets the output.
		 *
		 * @access public
		 * @param string $output
		 * @return self
		 */
		public function setOutputPath($output)
		{
			$this->_output = $output;
			return $this;
		}

		/**
		 * Gets the output.
		 *
		 * @access public
		 * @return string
		 */
		public function getOutputPath()
		{
			return $this->_output;
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
			$this->_add($this->_pre_input_commands, $command, $argument, $allow_command_repetition);
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
			$this->_add($this->_post_input_commands, $command, $argument, $allow_command_repetition);
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
			$this->_add($this->_post_output_commands, $command, $argument, $allow_command_repetition);
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
		 * Returns a pre input command.
		 *
		 * @access public
		 * @param string $command
		 * @return mixed boolean if failure or value if exists.
		 */
		public function getPreInputCommand($command)
		{
			if($this->hasPreInputCommand($command) === false)
			{
				return false;
			}
			
			return $this->_pre_input_commands[$command];
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
		 * Returns a pre input command.
		 *
		 * @access public
		 * @param string $command
		 * @return mixed boolean if failure or value if exists.
		 */
		public function getCommand($command)
		{
			if($this->hasCommand($command) === false)
			{
				return false;
			}
			
			return $this->_post_input_commands[$command];
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
			return isset($this->_post_output_commands[$command]) === true ? ($this->_post_output_commands[$command] === false ? true : $this->_post_output_commands[$command]): false;
		}
		
		/**
		 * Returns a post output command.
		 *
		 * @access public
		 * @param string $command
		 * @return mixed boolean if failure or value if exists.
		 */
		public function getPostOutputCommand($command)
		{
			if($this->hasPostOutputCommand($command) === false)
			{
				return false;
			}
			
			return $this->_post_output_commands[$command];
		}
		
		/**
		 * Combines the commands stored into a string
		 *
		 * @access protected
		 * @return string
		 */
		protected function _combineCommands()
		{
			if($this->_combined === true)
			{
				return;
			}
			$this->_combined = true;
			
			$args = $this->_arguments;
			$this->_arguments = array();
			
//			add the pre input commands
			if(empty($this->_pre_input_commands) === false)
			{
				$this->addCommands($this->_pre_input_commands);
			}
			
//			add in the input
			if(empty($this->_input) === false)
			{
				$this->add('-i')
					 ->add($this->_input);
			}
			
//			build the post input commands
			if(empty($this->_post_input_commands) === false)
			{
				$this->addCommands($this->_post_input_commands);
			}
			if(empty($args) === false)
			{
				$this->_arguments = array_merge($this->_arguments, $args);
			}
			
//			add in the output
			if(empty($this->_output) === false)
			{
				$this->add($this->_output);
			}
			
//			build the post output commands
			if(empty($this->_post_output_commands) === false)
			{
				$this->addCommands($this->_post_output_commands);
			}
		}
		
		public function getCommandString()
		{
			$this->_combineCommands();
			return parent::getCommandString();
		}
		
		/**
		 * Get the ExecBuffer object by combining the commands the creating in the buffer.
		 *
		 * @access protected
		 * @author Oliver Lillie
		 * @return void
		 */
	    protected function _getExecBuffer()
	    {
			$this->_combineCommands();
			return parent::getExecBuffer();
		}
		
		/**
		 * Get the initialised ExecBuffer object.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
	    public function &getExecBuffer()
	    {
			if(empty($this->_exec) === true)
			{
				$this->_exec = $this->_getExecBuffer();
			}
			return $this->_exec;
		}
		
		/**
		 * Execute the buffer command.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return self
		 */
		public function execute()
		{
			$this->getExecBuffer()
				 ->setBlocking(true)
				 ->execute();
			
			return $this;
		}
		
		protected function _callExecBufferFunction($function, $arguments=array())
		{
			if(empty($this->_exec) === true)
			{
				throw new Exception('The ExecBuffer object has not yet been generated. Please call getExecBuffer() before calling '.$function.'.');
			}
			
			if(is_callable(array($this->_exec, $function)) === false)
			{
				throw new Exception('This function is not callable within ExecBuffer.');
			}
			
			return call_user_func_array(array($this->_exec, $function), $arguments);
		}
		
		public function getRunTime()
		{
			return $this->_callExecBufferFunction('getRunTime');
		}
		
		public function getBuffer()
		{
			return $this->_callExecBufferFunction('getBuffer');
		}
		
		public function getRawBuffer()
		{
			return $this->_callExecBufferFunction('getRawBuffer');
		}
		
		public function getLastLine()
		{
			return $this->_callExecBufferFunction('getLastLine');
		}
		
		public function getLastSplit()
		{
			return $this->_callExecBufferFunction('getLastSplit');
		}
		
		public function getErrorCode()
		{
			return $this->_callExecBufferFunction('getErrorCode');
		}
		
		public function hasError()
		{
			return $this->_callExecBufferFunction('hasError');
		}
		
		public function isCompleted()
		{
			return $this->_callExecBufferFunction('isCompleted');
		}
	}
