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
    class ProgressHandlerPortable extends ProgressHandlerDefaultData
    {
        protected $_config;
        
        protected $_callback;
        protected $_total_duration;
        
        protected $_process_id;
        protected $_temp_id;
        protected $_boundary;
        protected $_time_started;
        
        private $_wait_on_next_probe;
        
        public $completed;
                 
        public function __construct($process_id, Config $config=null, $callback=null)
        {
            if($callback !== null && is_callable($callback) === false)
            {
                throw new Exception('The progress handler callback is not callable.');
            }
            
            $this->_config = $config === null ? Config::getInstance() : $config;
            
            if(empty($process_id) === true)
            {
                throw new Exception('The process id must not be empty.');
            }
            $this->_process_id = $process_id;
            
            list($temp_id, $boundary, $time_started) = explode('.', $this->_process_id);
            $this->_temp_id = $temp_id;
            $this->_boundary = $boundary;
            $this->_time_started = $time_started;
            
            $this->_output = $this->_config->temp_directory.'/phpvideotoolkit_'.$temp_id;
            if(is_file($this->_output) === false)
            {
                throw new Exception('The process output file cannot be found. Please make sure that another process has not garbage collected the file.');
            }
            
            $this->completed = null;
            $this->_callback = $callback;
            $this->_total_duration = 0;
            $this->_ffmpeg_process = null;
            $this->_wait_on_next_probe = false;
        }
        
        public function probe($probe_then_wait=false, $seconds=1)
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
        
        protected function _processOutputFile()
        {
//          setup the data to return.
            $return_data = $this->_getDefaultData();
            
//          load up the data             
            $completed = false;
            $raw_data = $this->_getRawData();
            
            if(empty($raw_data) === false)
            {
//              parse the raw data into the return data
                $this->_parseOutputData($return_data, $raw_data);
                
//              check to see if the process has completed
                if($return_data['percentage'] >= 100)
                {
                    $return_data['percentage'] = 100;
                    $return_data['run_time'] = filemtime($this->_output)-$this->_time_started;
                }
//              or if it has been interuptted 
                else if($return_data['interrupted'] === true)
                {
                    $return_data['run_time'] = filemtime($this->_output)-$this->_time_started;
                }
                else
                {
                    $return_data['run_time'] = time()-$this->_time_started;
                }
            }
            
//          check for any errors encountered by the parser
            $this->_checkOutputForErrorsFailureOrSuccess($raw_data, $return_data);
            
//          has the process completed itself?
            $this->completed = $return_data['completed'];
            if($this->completed === true)
            {
                unlink($this->_output);
            }
            
            return $return_data;
        }
        
        protected function _checkOutputForErrorsFailureOrSuccess($raw_data, &$return_data)
        {
            $failure_boundary = '<f-'.$this->_boundary.'>';
            $completion_boundary = '<c-'.$this->_boundary.'>';
            $error_code_boundary = '<e-'.$this->_boundary.'>';
            
            if(strpos($raw_data, $failure_boundary) !== false)
            {
                $lines = explode(PHP_EOL, $raw_data);

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

                $return_data['finished'] = true;
            }
            
            if(strpos($raw_data, $completion_boundary) !== false)
            {
                $return_data['completed'] = true;
                $return_data['finished'] = true;
            }
            
            if(strpos($raw_data, $error_code_boundary) !== false)
            {
                if(preg_match('/'.$error_code_boundary.'([0-9]+)/', $raw_data, $matches) > 0)
                {
                    $return_data['error'] = $matches[1];
                }
            }
        }

        protected function _parseOutputData(&$return_data, $raw_data)
        {
            $return_data['started'] = true;

//          get the total duration from the output
            $total_duration = '00:00:00.00';
            if(preg_match('/Duration:\s+([^,]*)/', $raw_data, $matches) > 0)
            {
                $total_duration = $matches[1];
            }            
            $total_duration = new Timecode($total_duration, Timecode::INPUT_FORMAT_TIMECODE);

            if(preg_match('/Input\s#0,\s+[^\s]+,\s+from\s+(.*):/', $raw_data, $input_matches) > 0)
            {
                $return_data['input_file'] = trim($input_matches[1], '\'"');
            }
            if(preg_match('/Output\s#0,\s+[^\s]+,\s+to\s+(.*):/', $raw_data, $output_matches) > 0)
            {
                $return_data['output_file'] = trim($output_matches[1], '\'"');
            }
            $return_data['process_file'] = $this->_output;
            
//          parse out the details of the data.
            if(preg_match_all(
                '/frame=\s*([0-9]+)\s'.
                'fps=\s*([0-9\.]+)\s'.
                'q=([0-9\.]+)\s'.
                '(L)?size=\s*([0-9\.bkBmg]+|N\/A)\s'.
                'time=\s*([0-9]{2,}:[0-9]{2}:[0-9]{2}.[0-9]+)\s'.
                'bitrate=\s*([0-9\.]+\s?[bkBmg\/s]+|N\/A)'.
                '(\sdup=\s*([0-9]+))?'.
                '(\sdrop=\s*([0-9]+))?'.
                '(\sdrop=\s*([0-9]+))?'.
                '/', $raw_data, $matches) > 0)
            {
                $last_key = count($matches[0])-1;
                $return_data['frame'] = $matches[1][$last_key];
                $return_data['fps'] = $matches[2][$last_key];
                $return_data['size'] = $matches[5][$last_key];
                $return_data['duration'] = new Timecode($matches[6][$last_key], Timecode::INPUT_FORMAT_TIMECODE);
                $return_data['percentage'] = ($return_data['duration']->total_seconds/$total_duration->total_seconds)*100;
                $return_data['dup'] = $matches[9][$last_key];
                $return_data['drop'] = $matches[11][$last_key];
                
//              this is a bit fuzzy
                if($matches[4][$last_key] === 'L')
                {
                    if($return_data['percentage'] < 99.5)
                    {
                        $return_data['interrupted'] = true;
                        $return_data['finished'] = true;
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
        }
         
        protected function _getRawData()
        {
            return file_get_contents($this->_output);
        }
     }
