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
		protected function _parseOutputData(&$return_data, $raw_data)
		{
			$return_data['started'] = true;

//			parse out the details of the data.
			if(preg_match_all(
				'/frame=\s*([0-9]+)\s'.
				'fps=\s*([0-9\.]+)\s'.
				'q=([0-9\.]+)\s'.
				'(L)?size=\s*([0-9\.bkBmg]+)\s'.
				'time=\s*([0-9]{2,}:[0-9]{2}:[0-9]{2}.[0-9]+)\s'.
				'bitrate=\s*([0-9\.]+\s?[bkBmg\/s]+)'.
				'(\sdup=\s*([0-9]+))?'.
				'(\sdrop=\s*([0-9]+))?'.
				'/', $raw_data, $matches) > 0)
			{
				$last_key = count($matches[0])-1;
				$return_data['frame'] = $matches[1][$last_key];
				$return_data['fps'] = $matches[2][$last_key];
				$return_data['size'] = $matches[5][$last_key];
				$return_data['duration'] = new Timecode($matches[6][$last_key], Timecode::INPUT_FORMAT_TIMECODE);
				$return_data['percentage'] = ($return_data['duration']->total_seconds/$this->_total_duration->total_seconds)*100;
				$return_data['dup'] = $matches[9][$last_key];
				$return_data['drop'] = $matches[11][$last_key];
					
				if($matches[4][$last_key] === 'L')
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
				if(count($matches[2]) === 1)
				{
					$return_data['fps_avg'] = $return_data['frame']/$return_data['run_time'];
				}
				else
				{
					$total_fps = 0;
					foreach ($matches[2] as $fps)
					{
						$total_fps += $fps;
					}
					$return_data['fps_avg'] = $total_fps/($last_key+1);
				}
			}
		}
		 
		protected function _getRawData()
		{
			return $this->_ffmpeg_process->getBuffer();
		}
	 }
