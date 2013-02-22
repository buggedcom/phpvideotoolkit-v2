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
	 * @access public
	 * @author Oliver Lillie
	 * @package default
	 */
	abstract class ProgressHandlerAbstract
	{
		protected $_is_non_blocking_comaptible = false;
		 
		protected $_callback;
		protected $_output_file;
		protected $_running;
		protected $_total_duration;
		protected $_start_time;
		protected $_callback_period_seconds;
		 
		public function __construct($callback=null, $callback_period_seconds=1)
		{
			if($callback !== null && is_callable($callback) === false)
			{
				throw new Exception('The progress handler callback is not callable.');
			}
			 
			$this->_callback_period_seconds = $callback_period_seconds;
			$this->_callback = $callback;
			$this->_running = false;
			$this->_total_duration = 0;
			$this->_start_time = null;
			$this->_output_file = null;
			
//			check to see if we have been supplied a callback, if so then it is no longer compatible 
//			with a non blocking save.
			if($this->_callback === null)
			{
				$this->_is_non_blocking_comaptible = false;
			}
		}
		
		public function getNonBlockingCompatibilityStatus()
		{
			return $this->_is_non_blocking_comaptible;
		}
		
		public function getOutputFile()
		{
			if($this->_output_file === null)
			{
				$tempfile = Factory::tempFile();
				$this->_output_file = $tempfile->file(null, 'txt');
			}
			
			return $this->_output_file;
		}

		public function setOutputFile($file_path)
		{
			if(is_file($file_path) === false)
			{
				throw new Exception('Output file does not exist.');
			}
			 
			$this->_output_file = $file_path;
		}

		public function setTotalDuration(Timecode $duration)
		{
			$this->_total_duration = $duration;
		}

		abstract public function setProgressExecCommands(ExecBuffer &$exec);
		
		abstract public function postProcessExecCommandsString(ExecBuffer &$exec, &$command_string);

		abstract public function getReadyToExecute(ExecBuffer &$exec, &$command_string);

		public function startHandler()
		{
			$this->_running = true;
			$this->_start_time = time()+microtime();
			$this->_run();
		}

		public function stopHandler()
		{
			$this->_running = false;
		}
		
		public function probe()
		{
			return $this->_processOutputFile();
		}

		protected function _run()
		{
			if($this->_running === false)
			{
				return;
			}
			
			if($this->_callback !== null)
			{
				$data = $this->_processOutputFile();
				call_user_func($this->_callback, $data);
			 
				$this->_wait();
				$this->_run();
			}
		}

		abstract protected function _readOutputFile();
		 
		protected function _getDefaultData()
		{
			return array(
				'output_file'=> $this->_output_file,
				'interrupted'=> false,
				'error'      => false,
				'started'    => false,
				'completed'  => false,
				'time_expired' => 0,
				'percentage' => 0,
				'fps_avg' 	 => 0,
				'size' 		 => 0,
				'frame' 	 => 0,
				'time' 		 => 0,
				'expected_duration' => $this->_total_duration,
				'fps' 		 => 0,
				'dup' 		 => 0,
				'drop' 		 => 0,
			);
		}
		
		protected function _processOutputFile()
		{
//			setup the data to return.
			$return_data = $this->_getDefaultData();

//			load up the data			 
			$raw_data = $this->_readOutputFile();
			if(empty($raw_data) === false)
			{
//				parse the raw data into the return data
				$this->_parseOutputData($return_data, $raw_data);
				
//				check for any errors encountered by the parser
				$this->_checkOutputForErrors($return_data, $raw_data);
				
//				check to see if the process has completed
				if($return_data['percentage'] === 100)
				{
					$return_data['completed'] = true;
					
//					automatically remove the output file.
					@unlink($this->_output_file);
					$return_data['output_file'] = null;
					
					$this->stopHandler();
				}
//				or if it has been interuptted 
				else if($return_data['interrupted'] === true)
				{
//					automatically remove the output file.
					@unlink($this->_output_file);
					$return_data['output_file'] = null;
					
					$this->stopHandler();
				}
			}
			 
			return $return_data;
		}

		protected function _wait()
		{
			usleep($this->_callback_period_seconds*100000);
		}
		 
		abstract protected function _parseOutputData(&$return_data, $raw_data);
		 
		abstract protected function _checkOutputForErrors(&$return_data, $raw_data);
	}
