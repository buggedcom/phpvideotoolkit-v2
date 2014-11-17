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
    abstract class ProgressHandlerAbstract extends ProgressHandlerDefaultData
    {
        protected $_config;
        
        protected $_is_non_blocking_compatible = true;
         
        protected $_ffmpeg_process;
        protected $_temp_directory;
        
        protected $_callback;
        
        private $_wait_on_next_probe;
        
        protected $_last_probe_data;

        public $completed;
                 
        public function __construct($callback=null, Config $config=null)
        {
            if($callback !== null && is_callable($callback) === false)
            {
                throw new \InvalidArgumentException('The progress handler callback is not callable.');
            }
            
            $this->_config = $config === null ? Config::getInstance() : $config;
            
            $this->completed = null;
            $this->_callback = $callback;
            $this->_total_duration = null;
            $this->_ffmpeg_process = null;
            $this->_wait_on_next_probe = false;
            $this->_last_probe_data = null;
            
//          check to see if we have been supplied a callback, if so then it is no longer compatible 
//          with a non blocking save.
            if($this->_callback !== null)
            {
                $this->_is_non_blocking_compatible = false;
            }
        }
        
        public function getNonBlockingCompatibilityStatus()
        {
            return $this->_is_non_blocking_compatible;
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
                if(is_int($seconds) === false && is_float($seconds) === false)
                {
                    throw new \InvalidArgumentException('$seconds must be an integer.');
                }
                else if($seconds <= 0)
                {
                    throw new \InvalidArgumentException('$seconds must be an integer greater than 0.');
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
                call_user_func($this->_callback, $data);
            }
        }
        
        public function attachFfmpegProcess(FfmpegProcess $process, Config $config=null)
        {
            if($config !== null)
            {
                $this->_config = $config;
            }
            $this->_ffmpeg_process = $process;
        }

        protected function _processOutputFile()
        {
//          setup the data to return.
            $return_data = $this->_getDefaultData();
            $return_data['run_time'] = $this->_ffmpeg_process->getRunTime();

//          load up the data             
            $completed = false;
            $raw_data = $this->_getRawData();
            
//          parse the raw data into the return data
            $this->_parseOutputData($return_data, $raw_data);
            
            if(empty($raw_data) === false)
            {
//              check to see if the process has completed
                if($return_data['percentage'] >= 100)
                {
                    $return_data['percentage'] = 100;
                }
            }
            
//          check for any errors encountered by the parser
            $this->_checkOutputForErrors($return_data);
            
//          has the process completed itself?
            $this->completed = $return_data['finished'];
            
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
