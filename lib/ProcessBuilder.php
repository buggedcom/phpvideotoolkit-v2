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
	class ProcessBuilder //extends Loggable
	{
	    protected $_program_path;
	    protected $_arguments;

	    public function __construct($program, $config=null)
	    {
			$this->_config = $config === null ? Config::getInstance() : $config;
			
			$path = $this->_config->{$program};
			$program = $path !== null ? $path : $program;
	        $this->_program_path = $program;

	        $this->_arguments = array();
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
			return $this->_program_path.' '.$this->_combineArgumentList($this->_arguments);
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
	        $exec = new ExecBuffer($this->getCommandString(), $this->_config->temp_directory);
			return $exec;
	    }
	}