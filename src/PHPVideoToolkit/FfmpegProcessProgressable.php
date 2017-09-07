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
     * If tbe ffmpeg process can be used in conjunction with a process handler, then this class is used to extend
     * the FfmpegProcess object.
     * 
     * @author Oliver Lillie
     */
    class FfmpegProcessProgressable extends FfmpegProcess 
    {
        /**
         * [$_output_renamed description]
         * @var [type]
         * @access private
         */
        private $_output_renamed;

        /**
         * [$_output_renamed description]
         * @var [type]
         * @access private
         */
        private $_final_output;

        /**
         * [$_output_renamed description]
         * @var [type]
         * @access private
         */
        private $_progress_callbacks;
        
        /**
         * Constructor
         *
         * @access public
         * @author: Oliver Lillie
         * @param string $program The programme to call. Note this is not the path. If you wish to call ffmpeg/aconv you should jsut
         *  supply 'ffmpeg' and then set the aconv path as the ffmpeg configuration option in Config.   
         * @param Config $config The config object.
         */
        public function __construct($programme, Config $config=null)
        {
            parent::__construct($programme, $config);
            
            $this->_progress_callbacks = array();
            $this->_output_renamed = null;
            $this->_final_output = null;
        }
        
        /**
         * Sets a command on ffmpeg that sets a timelimit for the process. If this timelimit is exceeded then ffmpeg bails.
         *
         * @access public
         * @author Oliver Lillie
         * @param integer $timelimit_in_seconds The timelimit to impose in seconds.
         * @return FfmpegProcess Returns the current object.
         * @throws FfmpegProcessCommandUnavailableException If the timelimit command is not available on the configured ffmpeg.
         * @throws \InvalidArgumentException If the timelimit is not an integer.
         * @throws \InvalidArgumentException If the timelimit is less than or equal to 0.
         */
        public function setProcessTimelimit($timelimit_in_seconds)
        {
            $parser = new FfmpegParser($this->_config);
            $commands = $parser->getCommands();
            if(isset($commands['timelimit']) === false)
            {
                throw new FfmpegProcessCommandUnavailableException('The -timelimit command is not supported by your version of FFmpeg.');
            }
            
            if(is_int($timelimit_in_seconds) === false)
            {
                throw new \InvalidArgumentException('The timelimit in seconds argument must be an integer.');
            }
            else if($timelimit_in_seconds <= 0)
            {
                throw new \InvalidArgumentException('The timelimit must be greater than 0 seconds.');
            }
            
            $this->addCommand('-timelimit', $timelimit_in_seconds);
            
            return $this;
        }
        
        /**
         * Attaches a progress handler to the ffmpeg progress. 
         * The progress handler is executed during the ffmpeg process.
         * Attaching a handler can cause a blocking process depending on the progress handler object or function used.
         *
         * @access public
         * @author Oliver Lillie
         * @param mixed $callback Can be a callable callback, or an object that extends PHPVideoToolkit\ProgressHandlerAbstract
         * @return FfmpegProcess Returns the current object.
         * @throws \InvalidArgumentException If the callback is an object and is not a subclass of PHPVideoToolkit\ProgressHandlerAbstract
         * @throws \InvalidArgumentException If the callback is not callable if not an object.
         */
        public function attachProgressHandler($callback)
        {
            if(is_object($callback) === true)
            {
                if(is_subclass_of($callback, 'PHPVideoToolkit\ProgressHandlerAbstract') === false)
                {
                    throw new \InvalidArgumentException('If supplying an object to attach as a progress handler, that object must inherit from ProgressHandlerAbstract.');
                }

                $callback->attachFfmpegProcess($this, $this->_config);
            }
            else if(is_callable($callback) === false)
            {
                throw new \InvalidArgumentException('The progress handler must either be a class that extends from ProgressHandlerAbstract or a callable function.');
            }
            
            array_push($this->_progress_callbacks, $callback);
            
            return $this;
        }
        
        /**
         * This function is used to execute the callback handlers when present.
         *
         * IMPORTANT! This is a protected function, however due to the nature of the 
         * callbacks, it must be public in order to be callable. 
         *
         * @access protected
         * @author Oliver Lillie
         * @return void
         */
        public function _executionCallbackRunner()
        {
            foreach($this->_progress_callbacks as $callback)
            {
                if(is_object($callback) === true)
                {
                    $callback->callback();
                }
                else
                {
                    call_user_func($callback, $this);
                }
            }
        }
        
        /**
         * Executes the ffmpeg process and can be supplied with an optional progress callback.
         *
         * @access public
         * @author Oliver Lillie
         * @param mixed $callback If given it must be a valid function that is callable.
         * @return FfmpegProcess Returns the current object.
         * @throws \InvalidArgumentException If the callback is not callable.
         */
        public function execute($callback=null)
        {
            if($callback !== null)
            {
                if(is_callable($callback) === false)
                {
                    throw new \InvalidArgumentException('Callback is not callable.');
                }

                $this->attachProgressHandler($callback);
            }
            
            if(empty($this->_progress_callbacks) === false)
            {
                $callback = array($this, '_executionCallbackRunner');
            }

            $this->getExecBuffer()
                 ->setBlocking(false)
                 ->execute($callback);
            
            return $this;
        }

        /**
         * Returns the output of the process if the process has completed.
         *
         * @access public
         * @author: Oliver Lillie
         * @param  mixed $post_process_callback
         * @return array Returns an array of output if more than 1 output file is expected, otherwise returns a string.
         */
        public function getOutput($post_process_callback=null)
        {
//          get the output of the process
            return $this->completeProcess($post_process_callback);
        }

        /**
         * Once the process has been completed this function can be called to return the output
         * of the process. Depending on what the process is outputting depends on what is returned.
         * If a single video or audio is being outputted then the related PHPVideoToolkit media object
         * will be returned. However if multiple files are being outputed then an array of the associated
         * objects are returned. Typically speaking an array will be returned when %index or %timecode
         * are within the output path.
         *
         * @access public
         * @author Oliver Lillie
         * @param  mixed $post_process_callback
         * @return mixed
         * @throws \InvalidArgumentException If a callback is supplied but is not callable.
         * @throws PHPVideoToolkit\FfmpegProcessOutputException If the function is called but the process has not completed
         *  yet.
         * @throws PHPVideoToolkit\FfmpegProcessOutputException If the process was aborted.
         * @throws PHPVideoToolkit\FfmpegProcessOutputException If the process completed with a termination signal.
         * @throws PHPVideoToolkit\FfmpegProcessOutputException If the process completed with an error.
         * @throws PHPVideoToolkit\FfmpegProcessOutputException If returned output is empty.
         * @throws PHPVideoToolkit\FfmpegProcessOutputException If returned output files does not exist.
         * @throws PHPVideoToolkit\FfmpegProcessOutputException If returned output files does exist but is a 0 byte file.
         */
        public function completeProcess($post_process_callback=null)
        {
            if($this->_final_output !== null)
            {
                return $this->_final_output;
            }
            
            if($post_process_callback !== null)
            {
                if(is_callable($post_process_callback) === false)
                {
                    throw new \InvalidArgumentException('The supplied post process callback is not callable.');
                }
            }

            if($this->isCompleted() === false)
            {
                throw new FfmpegProcessOutputException('Encoding has not yet started.');
            }
            
//          check for an error.
            if($this->hasError() === true)
            {
//              check for specific recieved signal errors.
                $last_split = $this->getLastSplit();
                if(preg_match('/Received signal ([0-9]+): terminating\./', $last_split, $matches) > 0)
                {
                    $kill_signals = array(
                        1 => 'Hang up detected on controlling terminal or death of controlling process.',
                        2 => 'User sent an interrupt signal.',
                        3 => 'User sent a quit signal.',
                        4 => 'Illegal instruction.',
                        6 => 'Abort signal from abort(3).',
                        8 => 'Floating point exception.',
                        9 => 'Kill signal sent.',
                        11 => 'Invalid memory reference',
                        13 => 'Broken pipe: write to pipe with no readers',
                        14 => 'Timer signal from alarm(2)',
                        15 => 'Termination signal sent.',
                        24 => 'Imposed time limit ({length} seconds) exceeded.',
                    );
                    // TODO add more signals.
                    $kill_int = (int) $matches[1];
                    if(isset($kill_signals[$kill_int]) === true)
                    {
                        $message = $kill_signals[$kill_int];
                        if($kill_int == 24)
                        {
                            $length = $this->getCommand('-timelimit');
                            $length = !$length ? 'unknown' : $length;
                            $message = str_replace('{length}', $length, $message);
                        }
                        throw new FfmpegProcessOutputException('Process was aborted. '.$message);
                    }
                    else
                    {
                        throw new FfmpegProcessOutputException('Termination signal received and the process aborted. Signal was '.$matches[1]);
                    }
                }
            
                throw new FfmpegProcessOutputException('Encoding failed and an error was returned from ffmpeg. Error code '.$this->getErrorCode().' was returned the message (if any) was: '.$last_split);
            }
            
            $output_path = $this->_renameMultiOutput();

            $output = array();
            foreach ($output_path as $key=>$path)
            {
                if(is_string($path) === true)
                {
//                  check for a none multiple file existence
                    if(empty($path) === true)
                    {
                        throw new FfmpegProcessOutputException('Unable to find output for the process as it was not set.');
                    }
                    else if(is_file($path) === false)
                    {
                        throw new FfmpegProcessOutputException('The output "'.$path.'", of the Ffmpeg process does not exist.');
                    }
                    else if(filesize($path) <= 0)
                    {
                        throw new FfmpegProcessOutputException('The output "'.$path.'", of the Ffmpeg process is a 0 byte file. Something must have gone wrong however it wasn\'t reported as an error by FFmpeg.');
                    }
                    
                    $output[$key] = $this->_convertPathToMediaObject($path);
                }
                else if(is_array($path) === true && empty($path) === false)
                {
                    $path_output = array();
                    foreach ($path as $file_path)
                    {
                        array_push($path_output, $this->_convertPathToMediaObject($file_path));
                    }
                    $output[$key] = $path_output;
                    unset($path_output);
                }
            }

            if(count($output) === 1)
            {
                $output = $output[0];
            }
                
//          do any post processing callbacks
            if($post_process_callback !== null)
            {
                $output = call_user_func($post_process_callback, $output, $this);
            }

            return $this->_final_output = $output;
        }

        /**
         * Checks to see if the ffmpeg output is a %d format and if so performs the rename of the output.
         *
         * @access protected
         * @author Oliver Lillie
         * @return mixed If %d output is expected then an array of path names is returned, otherwise a string path is returned.
         */
        protected function _renameMultiOutput()
        {
//          check to see if the output has already been renamed somewhere
            if($this->_output_renamed !== null)
            {
                return $this->_output_renamed;
            }

//          get the output of the process
            $paths = array();
            $output_count = $this->getOutputCount();
            for($i=0; $i<$output_count; $i++)
            {
                $path = $this->getOutputPath($i);

//              we have the output path but we now need to treat differently dependant on if we have multiple file output.
                if(preg_match('/\.(\%([0-9]*)d)\.([0-9\.]+_[0-9\.]+\.)?_(i|t)\./', $path) > 0)
                {
                    $path = $this->_renamePercentDOutput($path);
                }
                array_push($paths, $path);
            }

            return $paths;
        }

        /**
         * Returns a Media object based class for the given file path.
         *
         * @access protected
         * @author Oliver Lillie
         * @param  string $output_path The file path to convert.
         * @return object Either "Media", "Video", "Audio" or "Image" PHPVideoToolkit objects.
         */
        protected function _convertPathToMediaObject($output_path)
        {
//          get the media class from the output.
//          create the object from the class name and return the new object.
            $media_class = $this->_findMediaClass($output_path);
            return new $media_class($output_path, $this->_config, null, false);
        }

        /**
         * Renames any output from ffmpeg that would have been outputted in a sequence, ie using %d. Typically used with imagery.
         *
         * @access public
         * @author Oliver Lillie
         * @param  string $output_path The string notation for the output path.
         * @return array Returns an array of modified file paths.
         */
        protected function _renamePercentDOutput($output_path)
        {
            $output = array();

//          we have the output path but we now need to treat differently dependant on if we have multiple file output.
            if(preg_match('/\.(\%([0-9]*)d)\.([0-9\.]+_[0-9\.]+\.)?_(i|t)\./', $output_path, $matches) > 0)
            {
//              determine what we have to rename all the files to.
                $convert_back_to = $matches[4] === 't' ? 'timecode' : (int) $matches[2];
                
//              get the glob path and then find all the files from this output
                $output_glob_path = str_replace($matches[0], '.*.'.$matches[3].'_'.$matches[4].'.', $output_path);
                $outputted_files = glob($output_glob_path);
                
//              sort the output naturally so that if there is no index padding that we get the frames in the correct order.
                natsort($outputted_files);

//              loop to rename the file and then create each output object.
                $timecode = null;
                foreach ($outputted_files as $path)
                {
                    if($convert_back_to === 'timecode')
                    {
//                      if the start timecode has not been generated then find the required from the path string.
                        if($timecode === null)
                        {
                            $matches[3] = rtrim($matches[3], '.');
                            $matches[3] = explode('_', $matches[3]);
                            $timecode = new Timecode((int) $matches[3][1], Timecode::INPUT_FORMAT_SECONDS, (float) $matches[3][0]);
                        }
                        else
                        {
                            $timecode->frame += 1;
                        }
                        $actual_path = preg_replace('/\.[0-9]{12}\.[0-9\.]+_[0-9\.]+\._t\./', $timecode->getTimecode('%hh_%mm_%ss_%ms', false), $path);
                    }
                    else
                    {
                        $actual_path = preg_replace('/\.([0-9]+)\._i\./', '$1', $path);
                    }
                    $actual_path = preg_replace('/\._u\.[0-9]{5}_[a-z0-9]{5}_[0-9]+\.u_\./', '.', $actual_path);
                    
                    rename($path, $actual_path);
                    
                    array_push($output, $actual_path);
                }
                unset($outputted_files);
                
                // TODO create the multiple image output
            }

            return $output;
        }
        
        /**
         * Attempts to read the data about the file given by $path and then returns the class
         * name of the related media object.
         *
         * @access protected
         * @author Oliver Lillie
         * @param string $path The file pathe of the file to find the media class for.
         * @return string Returns the class name of the PHPVideoToolkit class related to the given $path argument.
         */
        protected function _findMediaClass($path)
        {
//          read the output to determine what it is so it can be post processed.
            $data = getimagesize($path);
            if(!$data)
            {
                $media_parser = new MediaParser($this->_config);
                $type = $media_parser->getFileType($path);
            }
            else if(strpos($data['mime'], 'image/') !== false)
            {
                $type = 'image';
            }
            else
            {
                $type = 'video';
            }
            
//          now we have the information switch between the types and create the return object.
            $class = 'Media';
            switch($type)
            {
                case 'audio' :
                case 'video' :
                case 'image' :
                    $class = '\\PHPVideoToolkit\\'.ucfirst(strtolower($type));
                    break;
            }
            
            return $class;
        }

    }
