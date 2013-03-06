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
		protected $_config;
		
		protected $_is_non_blocking_comaptible = true;
		 
		protected $_ffmpeg_process;
		protected $_temp_directory;
		
		protected $_callback;
		protected $_total_duration;
		
		private $_wait_on_next_probe;
		
		public $completed;
				 
		public function __construct($callback=null, Config $config=null)
		{
			if($callback !== null && is_callable($callback) === false)
			{
				throw new Exception('The progress handler callback is not callable.');
			}
			
			$this->_config = $config === null ? Config::getInstance() : $config;
			
			$this->completed = null;
			$this->_callback = $callback;
			$this->_total_duration = 0;
			$this->_ffmpeg_process = null;
			$this->_wait_on_next_probe = false;
			
//			check to see if we have been supplied a callback, if so then it is no longer compatible 
//			with a non blocking save.
			if($this->_callback !== null)
			{
				$this->_is_non_blocking_comaptible = false;
			}
		}
		
		public function getNonBlockingCompatibilityStatus()
		{
			return $this->_is_non_blocking_comaptible;
		}
		
		public function setTotalDuration(Timecode $duration)
		{
			$this->_total_duration = $duration;
			
			return $this;
		}

		public function probe($probe_then_wait=true, $seconds=1)
		{
			if($this->_wait_on_next_probe === true)
			{
				if(is_int($seconds) === false)
				{
					throw new Exception('$seconds must be an integer.');
				}
				else if($seconds <= 0)
				{
					throw new Exception('$seconds must be an integer greater than 0.');
				}
				
				usleep($seconds*100000);
			}
			
			$this->_wait_on_next_probe = $probe_then_wait;
			
			return $this->_processOutputFile();
		}
		
		public function callback()
		{
			if(is_callable($this->_callback) === true)
			{
				$data = $this->_processOutputFile();
				call_user_func($this->_callback, $data, $output_object);
			}
		}
		
		public function attachFfmpegProcess(FfmpegProcess $process, $process_temp_directory)
		{
			if($this->_temp_directory === null)
			{
				$this->setTempDirectory($process_temp_directory);
			}
			$this->_ffmpeg_process = $process;
		}

		protected function _getDefaultData()
		{
			return array(
				'interrupted'=> false,
				'error'      => false,
				'error_message' => null,
				'started'    => false,
				'finished'   => false,
				'completed'  => false,
				'run_time'   => 0,
				'percentage' => 0,
				'fps_avg' 	 => 0,
				'size' 		 => 0,
				'frame' 	 => 0,
				'duration'   => 0,
				'expected_duration' => $this->_total_duration,
				'fps' 		 => 0,
				'dup' 		 => 0,
				'drop' 		 => 0,
				'output_file'=> null,
			);
		}
		
		protected function _processOutputFile()
		{
//			setup the data to return.
			$return_data = $this->_getDefaultData();
			$return_data['run_time'] = $this->_ffmpeg_process->getRunTime();

//			load up the data			 
			$completed = false;
			$raw_data = $this->_getRawData();
			if(empty($raw_data) === false)
			{
//				parse the raw data into the return data
				$this->_parseOutputData($return_data, $raw_data);
				
//				check to see if the process has completed
				if($return_data['percentage'] >= 100)
				{
					$return_data['finished'] = true;
					$return_data['percentage'] = 100;
					$return_data['output_file'] = $this->_ffmpeg_process->getOutputPath();
				}
//				or if it has been interuptted 
				else if($return_data['interrupted'] === true)
				{
				}
			}
			
//			check for any errors encountered by the parser
			if($this->_checkOutputForErrors($return_data) === true)
			{
			}
			
//			has the process completed itself?
			$this->completed = $this->_ffmpeg_process->isCompleted();
			
			return $return_data;
		}

		protected function _checkOutputForErrors(&$return_data)
		{
			if($this->_ffmpeg_process->hasError() === true)
			{
				$lines = explode(PHP_EOL, trim($this->_ffmpeg_process->getBuffer()));

				$return_data['error'] = true;
				$error_lines = array();
				while(true)
				{
					$line = array_pop($lines);
					if(substr($line, 0, 1) === ' ')
					{
						break;
					}
					
					array_push($error_lines, $line);
				}
				
				if(empty($error_lines) === false)
				{
					$error_lines = array_reverse($error_lines);
					$return_data['error_message'] = implode(' ', $error_lines);
				}
				
				return true;
			}
			
			return false;
		}

		abstract protected function _getRawData();
		 
		abstract protected function _parseOutputData(&$return_data, $raw_data);
	}
