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
		public function __construct($callback=null, Config $config=null)
		{
//			check that the "-progress" function is available.
			$parser = new FfmpegParser($config);
			$available_commands = $parser->getCommands();
			if(isset($available_commands['progress']) === false)
			{
				throw new Exception('Your version of FFmpeg cannot support the Native progress handler. Please use ProgressHandlerOutput instead.');
			}

			parent::__construct($callback, $config);
			
			$this->_progress_file = null;
		}
		
		protected function _getRawData()
		{
//			there is a problem reading from the chunking file, so we must copy and then read, then delete the copy
//			in order to succesfully read the data.
			$copy = $this->_progress_file.'.'.time().'.txt';
			copy($this->_progress_file, $copy);
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
				$return_data['frame'] = $parts[$last_key]['frames'];
				$return_data['fps'] = $parts[$last_key]['fps'];
				$return_data['size'] = $parts[$last_key]['total_size'];
				$return_data['duration'] = new Timecode($parts[$last_key]['out_time'], Timecode::INPUT_FORMAT_TIMECODE);
				$return_data['percentage'] = ($return_data['duration']->total_seconds/$this->_total_duration->total_seconds)*100;
				$return_data['dup'] = $parts[$last_key]['dup_frames'];
				$return_data['drop'] = $parts[$last_key]['drop_frames'];
					
				if($parts[$last_key]['progress'] === 'end')
				{
					if($return_data['percentage'] < 99.5)
					{
						$return_data['interrupted'] = true;
					}
					else
					{
						$return_data['percentage'] = 100;
					}
				}
					
//				work out the fps average for performance reasons
				if(count($parts) === 1)
				{
					$return_data['fps_avg'] = $return_data['frame']/$return_data['run_time'];
				}
				else
				{
					$total_fps = 0;
					foreach ($parts as $part)
					{
						$total_fps += $part['fps'];
					}
					$return_data['fps_avg'] = $total_fps/($last_key+1);
				}
			}
		}
		 
		public function attachFfmpegProcess(FfmpegProcess $process, $temp_directory)
		{
			parent::attachFfmpegProcess($process, $temp_directory);

			$this->_progress_file = tempnam($this->_config->temp_directory, 'phpvideotoolkit_progress_');
			$this->_ffmpeg_process->addCommand('-progress', $this->_progress_file);
		}
	 }
