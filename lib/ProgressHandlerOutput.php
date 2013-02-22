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
	class ProgressHandlerOutput extends ProgressHandlerAbstract
	{
		protected $_is_non_blocking_comaptible = false;

		protected function _parseOutputData(&$return_data, $raw_data)
		{
			$return_data['started'] = true;

//			parse out the details of the data.
			// frame=   96 fps=0.0 q=0.0 size=      75kB time=00:00:03.49 bitrate= 176.2kbits/s dup=19 drop=0 
			if(preg_match_all('/frame=\s*([0-9]+)\sfps=\s*([0-9]+)\sq=([0-9\.]+)\s(L)?size=\s*([0-9bkBmg]+)\stime=\s*([0-9]{2,}:[0-9]{2}:[0-9]{2}.[0-9]+)\sbitrate=\s*([0-9\.a-z\/]+)\sdup=\s*([0-9]+)\sdrop=\s*([0-9]+)/', $raw_data, $matches) > 0)
			{
				$last_key = count($matches[0])-1;
				if($last_key > 0)
				{
					$return_data['frame'] = $matches[1][$last_key];
					$return_data['fps'] = $matches[2][$last_key];
					$return_data['size'] = $matches[5][$last_key];
					$return_data['time'] = new Timecode($matches[6][$last_key], Timecode::INPUT_FORMAT_TIMECODE);
					$return_data['time_expired'] = (time()+microtime())-$this->_start_time;
					$return_data['percentage'] = ($return_data['time']->total_seconds/$this->_total_duration->total_seconds)*100;
					$return_data['dup'] = $matches[8][$last_key];
					$return_data['drop'] = $matches[9][$last_key];
					
					if($matches[4][$last_key] === 'L' && $return_data['percentage'] < 99.5)
					{
						$return_data['interrupted'] = true;
					}
				}
					
//				work out the fps average for performance reasons
				$total_fps = 0;
				foreach ($matches[2] as $fps)
				{
					$total_fps += $fps;
				}
				$return_data['fps_avg'] = $total_fps/($last_key+1);
			}
		}
		 
		protected function _readOutputFile()
		{
			$contents = '';
			$handle = fopen($this->_output_file, 'r');
			if($handle !== false)
			{
				while (($buffer = fgets($handle, 4096)) !== false)
				{
					$contents .= $buffer;
			    }
			    fclose($handle);
			}
			return $contents;
		}
		 
		protected function _checkOutputForErrors(&$return_data, $raw_data)
		{
			// none
		}

		public function setProgressExecCommands(ExecBuffer &$exec)
		{
			// none
		}
		
		public function postProcessExecCommandsString(ExecBuffer &$exec, &$command_string)
		{
//			get a temporary file so that the progress handler can track the output.
//			and assign it to the progress handler so that the progress handler can read how
//			the video file is progressing.

//			if we have a progress handler, start it now.
			$output_file = $this->getOutputFile($this);
			$buffer_ouput = escapeshellarg($output_file);
			$command_string .= ' > '.$buffer_ouput.' 2>&1 &';
		}

		public function getReadyToExecute(ExecBuffer &$exec, &$command_string)
		{
			Trace::vars($command_string);
		}
		
	 }
