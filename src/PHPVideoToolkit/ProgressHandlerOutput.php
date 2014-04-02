<?php
    
    /**
     * This file is part of the PHP Video Toolkit v2 package.
     *
     * @author Oliver Lillie (aka buggedcom) <publicmail@buggedcom.co.uk>
     * @license Dual licensed under MIT and GPLv2
     * @copyright Copyright (c) 2008-2014 Oliver Lillie <http://www.buggedcom.co.uk>
     * @package PHPVideoToolkit V2
     * @version 2.1.1
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
            $return_data['status'] = 'pending';
            $return_data['started'] = true;

            if(empty($raw_data) === true)
            {
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
//          parse out the details of the data.
//          fucking non standardness in ffmpeg. I'm sure there is a reason for it but for fucks sake there has to be a better way
            if($return_data['output_count'] > 1)
            {
                $q_regex_array = array();
                $q_regex = '(?<lastq>L)?q=(?<q>[0-9\.]+)\s';
                for($i=0; $i<$return_data['output_count']; $i++)
                {
                    array_push($q_regex_array, str_replace('<q>', '<q'.$i.'>', str_replace('<lastq>', '<lastq'.$i.'>', $q_regex)));
                }
                $q_regex = implode('', $q_regex_array);
                $size_regex = 'size=\s*(?<size>[0-9\.bkBmg]+)\s';
            }
            else
            {
                $q_regex = 'q=(?<q0>[0-9\.]+)\s';
                $size_regex = '(?<lastsize>L)?size=\s*(?<size>[0-9\.bkBmg]+)\s';
            }
            $regex = 
                '/'.
                'frame=\s*(?<frame>[0-9]+)\s'.
                'fps=\s*(?<fps>[0-9\.]+)\s'.
                $q_regex.
                $size_regex.
                'time=\s*(?<time>[0-9]{2,}:[0-9]{2}:[0-9]{2}.[0-9]+)\s'.
                'bitrate=\s*(?<bitrate>[0-9\.]+\s?[bkitsBmg\/s]+)'.
                '(\sdup=\s*(?<dup>[0-9]+))?'.
                '(\sdrop=\s*(?<drop>[0-9]+))?'.
                '/';
                // Trace::vars($raw_data);
                // Trace::vars($regex);
                // Trace::vars(preg_match_all($regex, $raw_data, $matches), $matches);
            if(preg_match_all($regex, $raw_data, $matches) > 0)
            {
                $return_data['status'] = 'encoding';

                $last_key = count($matches[0])-1;
                $return_data['frame'] = $matches['frame'][$last_key];
                $return_data['fps'] = $matches['fps'][$last_key];
                $return_data['size'] = $matches['size'][$last_key];
                $return_data['duration'] = new Timecode($matches['time'][$last_key], Timecode::INPUT_FORMAT_TIMECODE);
                $return_data['percentage'] = ($return_data['duration']->total_seconds/$this->_total_duration->total_seconds)*100;
                $return_data['dup'] = $matches['dup'][$last_key];
                $return_data['drop'] = $matches['drop'][$last_key];
                
                $is_last = false;
                if($return_data['output_count'] > 1)
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
                else if($matches['lastsize'][$last_key] === 'L')
                {
                    $is_last = true;
                }
                if($is_last === true)
                {
                    $return_data['finished'] = true;
                    if($return_data['percentage'] < 99.5)
                    {
                        $return_data['interrupted'] = true;
                        $return_data['status'] = 'interrupted';
                    }
                    else
                    {
                        $return_data['percentage'] = 100;
                        $return_data['completed'] = true;
                        $return_data['status'] = 'completed';
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
                $return_data['status'] = 'decoding';
            }
            else
            {
                $return_data['status'] = 'error';
            }
        }
         
        protected function _getRawData()
        {
            return $this->_ffmpeg_process->getBuffer();
        }
     }
