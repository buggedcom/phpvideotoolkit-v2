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
	class ProgressHandlerNative extends ProgressHandlerAbstract
	{
		protected $_is_non_blocking_comaptible = true;

		public function __construct($callback=null, $callback_period_seconds=1)
		{
//			check that the "-progress" function is available.
			$parser = Factory::ffmpegParser();
			$available_commands = $parser->getCommands();
			if(isset($available_commands['progress']) === false)
			{
				throw new Exception('Your version of FFmpeg cannot support the Native progress handler. Please use ProgressHandlerOutput instead.');
			}

			parent::__construct($callback, $callback_period_seconds);
		}
		
		protected function _readOutputFile()
		{
//			there is a problem reading from the chunking file, so we must copy and then read, then delete the copy
//			in order to succesfully read the data.
			$copy = $this->_output_file.'.'.time().'.txt';
			copy($this->_output_file, $copy);
			$data = file_get_contents($copy);
			@unlink($copy);
			return $data;
		}
		 
		protected function _parseOutputData(&$return_data, $raw_data)
		{
			$return_data['started'] = true;
			
//			parse out the details of the data into the seperate chunks.
			$parts = preg_split('/frame=/', $raw_data);
			array_shift($parts);
			foreach ($parts as $key=>$part)
			{
				$data_parts = preg_split('/=|\r\n|\r|\n/', trim($part));
				$data = array(
					'frames' => $data_parts[0],
				);
				for($i=1, $l=count($data_parts)-1; $i<$l; $i+=2)
				{
					$data[$data_parts[$i]] = $data_parts[$i+1];
				}
				$parts[$key] = $data;
			}
			if(empty($parts) === false)
			{
				$last_key = count($parts)-1;
				if($last_key > 0)
				{
					$return_data['frame'] = $parts[$last_key]['frames'];
					$return_data['fps'] = $parts[$last_key]['fps'];
					$return_data['size'] = $parts[$last_key]['total_size'];
					$return_data['time'] = new Timecode($parts[$last_key]['out_time'], Timecode::INPUT_FORMAT_TIMECODE);
					$return_data['time_expired'] = (time()+microtime())-$this->_start_time;
					$return_data['percentage'] = ($return_data['time']->total_seconds/$this->_total_duration->total_seconds)*100;
					$return_data['dup'] = $parts[$last_key]['dup_frames'];
					$return_data['drop'] = $parts[$last_key]['drop_frames'];
					
					if($parts[$last_key]['progress'] === 'end' && $return_data['percentage'] < 99.5)
					{
						$return_data['interrupted'] = true;
					}
				}
					
//				work out the fps average for performance reasons
				$total_fps = 0;
				foreach ($parts as $part)
				{
					$total_fps += $part['fps'];
				}
				$return_data['fps_avg'] = $total_fps/($last_key+1);
			}
		}
		 
		protected function _checkOutputForErrors(&$return_data, $raw_data)
		{
			
		}

		public function setProgressExecCommands(ExecBuffer &$exec)
		{
			$exec->addCommand('-progress', $this->getOutputFile());
		}
		
		public function postProcessExecCommandsString(ExecBuffer &$exec, &$command_string)
		{
			if($exec->getNonBlocking() === false)
			{
				$command_string .= ' > /dev/null 2>&1 &';
			}
		}

		public function getReadyToExecute(ExecBuffer &$exec, &$command_string)
		{
			Trace::vars($command_string);
		}
		
	 }
