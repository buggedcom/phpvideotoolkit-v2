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
    class ProgressHandlerNative extends ProgressHandlerAbstract
    {
        protected $_progress_file;
        protected $_input;
        protected $_output;
        
        public function __construct($callback=null, Config $config=null)
        {
//          check that the "-progress" function is available.
            $parser = new FfmpegParser($config);
            $available_commands = $parser->getCommands();
            if(isset($available_commands['progress']) === false)
            {
                throw new Exception('Your version of FFmpeg cannot support the Native progress handler. Please use ProgressHandlerOutput instead.');
            }

            parent::__construct($callback, $config);
            
            $this->_progress_file = null;
            $this->_input = null;
            $this->_output = null;
        }
        
        protected function _getRawData()
        {
            if(is_file($this->_progress_file) === false)
            {
                return '';
            }

//          there is a problem reading from the chunking file, so we must copy and then read, then delete the copy
//          in order to succesfully read the data.
            $copy = $this->_progress_file.'.'.time().'.txt';
            copy($this->_progress_file, $copy);
            $data = file_get_contents($copy);
            @unlink($copy);
            return $data;
        }
         
        protected function _parseOutputData(&$return_data, $raw_data)
        {
            $return_data['started'] = true;

            $return_data['input_count'] = count($this->_input);
            $return_data['input_file'] = $return_data['input_count'] === 1 ? $this->_input[0] : $this->_input;

            $return_data['output_count'] = count($this->_output);
            $return_data['output_file'] = $return_data['output_count'] === 1 ? $this->_output[0] : $this->_output;

            if(empty($raw_data) === true)
            {
                if(empty($this->_last_probe_data) === true)
                {
                    $return_data['status'] = self::ENCODING_STATUS_PENDING;
                }
                else
                {
                    $return_data['percentage'] = 100;
                    $return_data['finished'] = true;
                    $return_data['status'] = self::ENCODING_STATUS_FINISHED;
                }
                return;
            }

            $return_data['status'] = self::ENCODING_STATUS_ENCODING;
            
            $return_data['process_file'] = $this->_progress_file;

//          parse out the details of the data into the seperate chunks.
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

            $ended = false;
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
                    $ended = true;
                    $return_data['finished'] = true;
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

            if($ended === true)
            {
                $this->_deleteProgressFile();

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

        protected function _deleteProgressFile()
        {
            @unlink($this->_progress_file);
        }
         
        public function attachFfmpegProcess(FfmpegProcess $process, Config $config=null)
        {
            parent::attachFfmpegProcess($process, $config);

            $this->_progress_file = tempnam($this->_config->temp_directory, 'phpvideotoolkit_progress_'.time().'_');
            $this->_input = $this->_ffmpeg_process->getAllInput();
            $this->_output = $this->_ffmpeg_process->getAllOutput();
            $this->_ffmpeg_process->addCommand('-progress', $this->_progress_file);
        }
     }
