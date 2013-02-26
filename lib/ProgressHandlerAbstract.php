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
		 
		protected $_ffmpeg_process;
		
		protected $_callback;
		protected $_output_file;
		protected $_running;
		protected $_total_duration;
		protected $_start_time;
		
		public $completed;
				 
		public function __construct($callback=null)
		{
			if($callback !== null && is_callable($callback) === false)
			{
				throw new Exception('The progress handler callback is not callable.');
			}
			 
			$this->completed = null;
			
			$this->_callback = $callback;
			$this->_running = false;
			$this->_total_duration = 0;
			$this->_start_time = null;
			$this->_output_file = null;
			$this->_ffmpeg_process = null;
			
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
		
		public function setTotalDuration(Timecode $duration)
		{
			$this->_total_duration = $duration;
			
			return $this;
		}

		public function probe()
		{
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
		
		public function attachFfmpegProcess(FfmpegProcess $process)
		{
			$this->_ffmpeg_process = $process;
		}

		protected function _getDefaultData()
		{
			return array(
				'interrupted'=> false,
				'error'      => false,
				'error_message' => null,
				'started'    => false,
				'completed'  => false,
				'run_time'   => 0,
				'percentage' => 0,
				'fps_avg' 	 => 0,
				'size' 		 => 0,
				'frame' 	 => 0,
				'time' 		 => 0,
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
					$return_data['completed'] = true;
					$return_data['percentage'] = 100;
					$return_data['output_file'] = $this->_ffmpeg_process->getOutput();
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
			
//			the handler is marked as completed if the processing is complete, interuptted or has an error.
			$this->completed = $return_data['complete'] === true || $return_data['interrupted'] === true || $return_data['error'] === true;;
			
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
