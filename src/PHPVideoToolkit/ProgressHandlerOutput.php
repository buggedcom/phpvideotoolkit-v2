<?php
    
    /**
     * This file is part of the PHP Video Toolkit v2 package.
     *
     * @author Oliver Lillie (aka buggedcom) <publicmail@buggedcom.co.uk>
     * @license Dual licensed under MIT and GPLv2
     * @copyright Copyright (c) 2008-2014 Oliver Lillie <http://www.buggedcom.co.uk>
     * @package PHPVideoToolkit V2
     * @version 2.1.7-beta
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

            if(empty($raw_data) === true)
            {
                if($this->_last_probe_data === null)
                {
                    $return_data['status'] = self::ENCODING_STATUS_PENDING;
                }
                else
                {
                    $return_data = $this->_last_probe_data;
                    $return_data['finished'] = true;
                    $return_data['status'] = self::ENCODING_STATUS_FINISHED;
                }
                return;
            }

            if(preg_match_all('/Input\s#[0-9]+,\s+[^\s]+,\s+from\s+(.*):/', $raw_data, $input_matches) > 0)
            {
                array_walk($input_matches[1], function(&$value)
                {
                    $value = trim($value, '\'"');
                });
                $return_data['input_count'] = count($input_matches[1]);
                $return_data['input_file'] = $return_data['input_count'] === 1 ? $input_matches[1][0] : $input_matches[1];
            }
            if(preg_match_all('/Output\s#[0-9]+,\s+[^\s]+,\s+to\s+(.*):/', $raw_data, $output_matches) > 0)
            {
                array_walk($output_matches[1], function(&$value)
                {
                    $value = trim($value, '\'"');
                });
                $return_data['output_count'] = count($output_matches[1]);
                $return_data['output_file'] = $return_data['output_count'] === 1 ? $output_matches[1][0] : $output_matches[1];
            }
            $return_data['process_file'] = $this->_ffmpeg_process->getBufferOutput();

//          determine how many video outs there are as that dictates the number of q= regexes to add as well as some others such as frame.
            $video_stream_count = preg_match_all('/\s*Stream\s*\#([0-9]+):1s*\(und\)\s*:/i', substr($raw_data, strpos($raw_data, 'Output #0')));
            $q_regex = '';
            $size_regex = '';
            $frame_regex = '';
            $fps_regex = '';
            if($video_stream_count > 0)
            {
                $frame_regex = 'frame=\s*(?<frame>[0-9]+)\s';
                $fps_regex = 'fps=\s*(?<fps>[0-9\.]+)\s';
            }

//          parse out the details of the data.
//          fucking non standardness in ffmpeg. I'm sure there is a reason for it but for fucks sake there has to be a better way
            if($video_stream_count > 1)
            {
                $q_regex_array = array();
                $q_regex = '(?<lastq>L)?q=(?<q>[0-9\.]+)\s';
                for($i=0; $i<$video_stream_count; $i++)
                {
                    array_push($q_regex_array, str_replace('<q>', '<q'.$i.'>', str_replace('<lastq>', '<lastq'.$i.'>', $q_regex)));
                }
                $q_regex = implode('', $q_regex_array);
                $size_regex = 'size=\s*(?<size>[0-9\.bkBmg]+|N\/A)\s';
            }
            else if($video_stream_count === 1)
            {
                $q_regex = 'q=(?<q0>[0-9\.]+)\s';
                $size_regex = '(?<lastsize>L)?size=\s*(?<size>[0-9\.bkBmg]+|N\/A)\s';
            }
            else
            {
                $size_regex = 'size=\s*(?<size>[0-9\.bkBmg]+|N\/A)\s';
            }

//          compile the regex dependant on the numebr of video streams
            $regex = 
                '/'.
                $frame_regex.
                $fps_regex.
                $q_regex.
                $size_regex.
                'time=\s*(?<time>[0-9]{2,}:[0-9]{2}:[0-9]{2}.[0-9]+)\s'.
                'bitrate=\s*(?<bitrate>[0-9\.]+\s?[bkitsBmg\/s]+|N\/A)'.
                '(\sdup=\s*(?<dup>[0-9]+))?'.
                '(\sdrop=\s*(?<drop>[0-9]+))?'.
                '/';

            if(preg_match_all($regex, $raw_data, $matches) > 0)
            {
                $return_data['status'] = self::ENCODING_STATUS_ENCODING;

                $last_key = count($matches[0])-1;
                $return_data['frame'] = isset($matches['frame']) === true ? $matches['frame'][$last_key] : null;
                $return_data['fps'] = isset($matches['fps']) === true ? $matches['fps'][$last_key] : null;
                $return_data['size'] = $matches['size'][$last_key];
                $return_data['duration'] = new Timecode($matches['time'][$last_key], Timecode::INPUT_FORMAT_TIMECODE);
                $return_data['percentage'] = ($return_data['duration']->total_seconds/$this->_total_duration->total_seconds)*100;
                $return_data['dup'] = $matches['dup'][$last_key];
                $return_data['drop'] = $matches['drop'][$last_key];
                
                $is_last = false;
                if($video_stream_count > 1)
                {
                    for($i=0; $i<$return_data['output_count']; $i++)
                    {
                        if(isset($matches['lastq'.$i]) === true && $matches['lastq'.$i][$last_key] === 'L')
                        {
                            $is_last = true;
                            break;
                        }
                    }
                }
                else if(isset($matches['lastsize']) === true && $matches['lastsize'][$last_key] === 'L')
                {
                    $is_last = true;
                }

//              if we have the last frame then signal that the process has finished.
                if($is_last === true)
                {
                    if($return_data['percentage'] < 99.5)
                    {
                        $return_data['interrupted'] = true;
                        $return_data['status'] = self::ENCODING_STATUS_INTERRUPTED;
                    }
                    else
                    {
                        $return_data['percentage'] = 100;
                    }
                }

//              work out the fps average for performance reasons
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
            else if(strpos($raw_data, 'Stream mapping:') !== false && strpos($raw_data, 'Press [q] to stop, [?] for help') !== false)
            {
                $return_data['status'] = self::ENCODING_STATUS_DECODING;
            }
            else
            {
                $return_data['status'] = self::ENCODING_STATUS_ERROR;
            }

            if($this->_ffmpeg_process->isCompleted() === true)
            {
                $return_data['finished'] = true;
                if($return_data['status'] !== self::ENCODING_STATUS_INTERRUPTED)
                {
                    $return_data['completed'] = true;
                    $return_data['status'] = self::ENCODING_STATUS_FINISHED;
                }
            }
            else if($return_data['percentage'] === 100)
            {
                $return_data['completed'] = true;
                $return_data['status'] = self::ENCODING_STATUS_COMPLETED;
            }
            else if($return_data['percentage'] >= 99.5)
            {
                $return_data['percentage'] = 100;
                $return_data['status'] = self::ENCODING_STATUS_FINALISING;
            }

            $this->_last_probe_data = $return_data;
        }
         
        protected function _getRawData()
        {
            return $this->_ffmpeg_process->getBuffer();
        }
     }
