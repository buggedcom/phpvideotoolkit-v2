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
	class ExecBuffer //extends Loggable
	{
		protected $_failure_tracking;
		protected $_blocking;
		protected $_output;
		protected $_temp_directory;
		
		protected $_executed_command;
		protected $_command;
		protected $_buffer;
		protected $_error_code;
			
		protected $_running;
		protected $_start_time;
		protected $_end_time;
		protected $_callback_period_interval;
		
		protected $_boundary;
		protected $_failure_boundary;
		protected $_completion_boundary;
		protected $_error_code_boundary;
		
		protected $_tmp_files;
		
		const DEV_NULL = '/dev/null';
		const TEMP = -1;
		
		public function __construct($exec_command_string, $temp_directory=null)
		{
			$this->setTempDirectory($temp_directory);
			
			$this->_failure_tracking = true;
			$this->_blocking = true;
			$this->_output = self::TEMP;
			$this->_buffer = null;
			$this->_error_code = null;
			
			$this->_running = false;
			$this->_start_time = null;
			$this->_end_time = null;
			$this->_callback_period_interval = 1;
			
			$this->_tmp_files = array();
			
			$id = uniqid(rand(99999, 999999).'-').'-'.md5(__FILE__);
			$this->_boundary = $id;
			$this->_error_code_boundary = '<e-'.$id.'>';
			$this->_failure_boundary = '<f-'.$id.'>';
			$this->_completion_boundary = '<c-'.$id.'>';
			
			$this->_command = $exec_command_string;
		}
		
		public function setTempDirectory($temp_directory=null)
		{
			if($temp_directory === null)
			{
				$temp_directory = sys_get_temp_dir();
			}
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
			
			return $this;
		}
		
		/**
		 * Destruct function autoamtically tidies up tmp files.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function __destruct()
		{
			if(empty($this->_tmp_files) === false)
			{
				foreach ($this->_tmp_files as $path)
				{
					//@unlink($path);
				}
			}
		}
		
		/**
		 * Start the exec, essentially call exec().
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function execute($callback=null)
		{
//			check the the callback is callable.
			if($callback !== null)
			{
				if(is_callable($callback) === false)
				{
					throw new Exception('The supplied callback is not callable.');
				}
				$this->setBlocking(true);
			}
			
//			get the execution string
			$this->_executed_command = $this->getExecString();

//			do the execution.
			$this->_running = true;
			$this->_start_time = time()+microtime();
			exec($this->_executed_command, $buffer, $err); // note error cannot be replied upon because of the output options.
			$buffer = implode(PHP_EOL, $buffer);
			$this->_error_code = $err;
			
//			do we need to process output buffer
			if(empty($this->_output) === false)
			{
//				if the process is blocking, the run
				if($this->_blocking === true)
				{
					if($callback !== null && is_callable($callback) === true)
					{
						call_user_func($callback, $this, null, null);
					}
					$this->_run($callback);
				}
			}
			
			if($this->_blocking === true)
			{
				$this->_end_time = time()+microtime();
			}
			
//			this is the final callback
			if($callback === null && is_callable($callback) === true)
			{
				call_user_func($callback, $this, null, true);
			}
			
			return $this;
		}
		
		/**
		 * Gets the current run time of the command execution.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function getRunTime()
		{
			if(empty($this->_start_time) === true)
			{
				throw new Exception('Unable to read runtime as command has not yet been executed.');
			}
			if(empty($this->_end_time) === false)
			{
				$end = $this->_end_time;
			}
			else
			{
				$end = time()+microtime();
			}
			
			return $end - $this->_start_time;
		}
		
		/**
		 * Runs the read/wait loop.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param mixed $callback 
		 * @return void
		 */
		protected function _run($callback)
		{
//			get the buffer regardless of wether or not there is a callback as it updates and 
//			checks for the completion of the command.
			$buffer = $this->getBuffer();
			
//			get the buffer to give to the
			if($callback !== null && is_callable($callback) === true)
			{
				call_user_func($callback, $this, $buffer, false);
			}
			
//			if we have finished running the loop then break here.
			if($this->_running === false)
			{
				return;
			}
			
//			still running so wait and then run again.
			$this->wait($this->_callback_period_interval);
			$this->_run($callback);
		}
		
		/**
		 * Makes the read/wait loop wait.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function wait($seconds=1)
		{
			usleep($seconds*100000);
		}
		
		/**
		 * Stops the read/wait loop from running.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function stop()
		{
			$this->_running = false;
		}
		
		/**
		 * Returns the buffer.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getRawBuffer()
		{
			if(empty($this->_output) === false)
			{
				$this->_buffer = file_get_contents($this->_output);
				$this->_detectCompletionAndEnd();
			}
			
			return $this->_buffer;
		}
		
		/**
		 * Returns the buffer.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getBuffer()
		{
			return rtrim(preg_replace(array('/'.$this->_failure_boundary.'/', '/'.$this->_completion_boundary.'/', '/'.$this->_error_code_boundary.'([0-9]+)/'), '', $this->getRawBuffer()));
		}
		
		/**
		 * Returns the buffer.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getLastLine()
		{
			$buffer = $this->getBuffer();
			$lines = preg_split('/\r\n|\r|\n/', $buffer);
			return array_pop($lines);
		}
		
		/**
		 * Returns the buffer.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getLastSplit()
		{
			$buffer = $this->getBuffer();
			$splits = explode(PHP_EOL, $buffer);
			return array_pop($splits);
		}
		
		/**
		 * Deletes the output buffer file.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return void
		 */
		public function deleteOutputFile()
		{
			if(empty($this->_output) === false && is_file($this->_output) === true)
			{
				@unlink($this->_output);
				$this->_output = null;
			}
		}
		
		/**
		 * Detects the failure boundary in the output.
		 *
		 * @access protected
		 * @author Oliver Lillie
		 * @param boolean $update_buffer 
		 * @return boolean
		 */
		protected function _detectFailureBoundaryInOutput($update_buffer=false)
		{
			if($this->_failure_tracking !== true)
			{
				throw new Exception('Failure tracking is not enabled. Unable to determine if command failed.');
			}
			
//			do we need to update the buffer?
			if($update_buffer === true)
			{
				$this->getBuffer();
			}
			
			return strpos($this->_buffer, $this->_failure_boundary) !== false;
		}
		
		public function getErrorCode()
		{
			return $this->_getErrorCodeInOutput();
		}
		
		public function hasError()
		{
			return $this->_detectFailureBoundaryInOutput(true);
		}
		
		public function isCompleted()
		{
			return $this->_detectCompletionBoundaryInOutput(true);
		}
		
		protected function _detectCompletionAndEnd()
		{
//			detect if the output has completed, and if it does
//			delete the output file and stop the loop.
			if($this->_detectCompletionBoundaryInOutput() === true)
			{
				$this->deleteOutputFile();
				$this->stop();
			}
			else if($this->_detectFailureBoundaryInOutput() === true)
			{
				$this->_error_code = $this->_getErrorCodeInOutput();
			}
		}
		
		protected function _getErrorCodeInOutput($update_buffer=false)
		{
//			do we need to update the buffer?
			if($update_buffer === true)
			{
				$this->getBuffer();
			}
			
			if(preg_match('/'.$this->_error_code_boundary.'([0-9]+)/', $this->_buffer, $matches) > 0)
			{
				return $matches[1];
			}
			return null;
		}
				
		
		/**
		 * Detects the completion boundary in the output.
		 *
		 * @access protected
		 * @author Oliver Lillie
		 * @param boolean $update_buffer 
		 * @return boolean
		 */
		protected function _detectCompletionBoundaryInOutput($update_buffer=false)
		{
//			do we need to update the buffer?
			if($update_buffer === true)
			{
				$this->getBuffer();
			}
			
			return strpos($this->_buffer, $this->_completion_boundary) !== false;
		}
		
		/**
		 * Returns an augmented command string based on the classes options.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getExecString()
		{
			$command_string = $this->_command;
			
//			get the output append string
			$output = $this->getBufferOutput();
			if(empty($output) === false)
			{
				$output = ' > '.escapeshellarg($output);
			}
			
//			get the buffer divert string.
			$buffer_divert = '';
			if(empty($output) === false || empty($tracking) === false)
			{
				$buffer_divert = ' 2>&1 &';
			}
			
//			get the failure tracking boundaries
//			and get the completion boundary
			$completion_boundary_open = '';
			$completion_boundary_close = '';
			if($this->_failure_tracking === true)
			{
				$completion_boundary_open = '((';
				$completion_boundary_close = ' && echo '.escapeshellarg($this->_completion_boundary).') || echo '.escapeshellarg($this->_failure_boundary).' '.escapeshellarg($this->_completion_boundary).' '.escapeshellarg($this->_error_code_boundary).'$?) 2>&1';
			}
			else
			{
				$completion_boundary_open = '(';
				$completion_boundary_close = ' && echo '.escapeshellarg($this->_completion_boundary).') 2>&1';
			}
			
//			compile the final command and track
			return $completion_boundary_open.$command_string.$completion_boundary_close.$output.$buffer_divert;
		}
		
		/**
		 * Returns the executred command.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getExecutedCommand()
		{
			if(empty($this->_executed_command) === true)
			{
				throw new Exception('The command has not yet been executed.');
			}
			
			return $this->_executed_command;
		}
		
		/**
		 * Returns the original command.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getCommand()
		{
			return $this->_command;
		}
		
		/**
		 * Sets the pid tracking status of the query.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $enable_pid_tracking 
		 * @return void
		 */
		public function setFailureTracking($enable_failure_tracking)
		{
			if(is_bool($enable_failure_tracking) === false && is_null($enable_failure_tracking) === false)
			{
				throw new Exception('$enable_failure_tracking must be a boolean (or null) value.');
			}
			
			$this->_failure_tracking = $enable_failure_tracking;
			return $this;
		}
		
		/**
		 * Gets the failure tracking status of the exec command.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return mixed
		 */
		public function getFailureTracking()
		{
			return $this->_failure_tracking;
		}
		
		/**
		 * Sets the callback period wait interval.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param integer $callback_period_interval
		 * @return void
		 */
		public function setCallbackWaitInterval($callback_period_interval)
		{
			if(is_int($pid) === false)
			{
				throw new Exception('$callback_period_interval must be an integer.');
			}
			
			$this->_callback_period_interval = $callback_period_interval;
			return $this;
		}
		
		/**
		 * Returns the pid of an executed command.
		 * If the command has not yet been executed then it will return null, unless a pid
		 * has been set by setPid();
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return mixed
		 */
		public function getCallbackWaitInterval()
		{
			return $this->_callback_period_interval;
		}
		
		/**
		 * Sets the exec calls blocking mode.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $enable_blocking 
		 * @return void
		 */
		public function setBlocking($enable_blocking)
		{
			if(is_bool($enable_blocking) === false && is_null($enable_blocking) === false)
			{
				throw new Exception('$enable_blocking must be a boolean (or null) value.');
			}
			
			$this->_blocking = $enable_blocking;
			return $this;
		}
		
		/**
		 * Returns the exec calls blocking mode.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return boolean
		 */
		public function getBlocking()
		{
			return $this->_blocking;
		}
		
		/**
		 * Sets the output of the exec call.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param mixed $enable_pid_tracking Can be one of the following constants. ExecBuffer::DEV_NULL, ExecBuffer::TEMP, 
		 *	a string will be interpretted as a file or null will output everything to sdout.
		 * @return void
		 */
		public function setBufferOutput($buffer_output)
		{
			if(in_array($buffer_output, array(null, self::DEV_NULL, self::TEMP)) === false)
			{
				$dir = dirname($buffer_ouput);
				if(is_dir($dir) === false)
				{
					throw new Exception('Buffer output parent directory "'.$dir.'" is not a directory.');
				}
				else if(is_readable($dir) === false || is_writeable($dir) === false)
				{
					throw new Exception('Buffer output parent directory "'.$dir.'" is not read-writable by the webserver.');
				}
			}
			
			$this->_output = $buffer_output;
			return $this;
		}
		
		/**
		 * Gets the status of the buffer output.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getBufferOutput()
		{
			if($this->_output === null)
			{
				if($this->_blocking === false)
				{
					return $this->_output = $this->generateTmpFile();
				}
			}
			else if($this->_output === self::TEMP)
			{
				return $this->_output = $this->generateTmpFile();
			}
			
			return $this->_output;
		}
		
		/**
		 * Generate and create a temp file.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function generateTmpFile($prefix='')
		{
			$tmp = tempnam($this->_temp_directory, 'phpvideotoolkit_'.$prefix);
			array_push($this->_tmp_files, $tmp);
			return $tmp;			
		}
		
	}