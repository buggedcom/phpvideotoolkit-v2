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
     * The very lowest base class in PHPVideoToolkit. It is the exec() wrapper than can determine if a call to exec fails or completes successfully.
     * It also returns any error messages encountered if the exec() fails.
     *
     * @author Oliver Lillie
     */
    class ExecBuffer
    {
        /**
         * Variable placeholder for determinining if we are tracking failures from the exec() call.
         * @var bool
         * @access protected
         */
        protected $_failure_tracking;

        /**
         * Variable placeholder for determining if the exec() call is to block php execution or be run in the background.
         * @var bool
         * @access protected
         */
        protected $_blocking;

        /**
         * Variable placeholder for the output file for the output from the exec() call.
         * @var string
         * @access protected
         */
        protected $_output;

        /**
         * Variable placeholder for the temp directory used for generating temp files. 
         * @var string
         * @access protected
         */
        protected $_temp_directory;
        
        /**
         * Variable placeholder for the final executed command to exec().
         * @var string
         * @access protected
         */
        protected $_executed_command;

        /**
         * Variable placeholder for the requested executed command to exec() without ExecBuffers wrapping.
         * @var string
         * @access protected
         */
        protected $_command;

        /**
         * Variable placeholder for the exec() buffer output which is found in the output file.
         * @var string
         * @access protected
         */
        protected $_buffer;

        /**
         * Variable placeholder for any error code encountered by ExecBuffer
         * @var string
         * @access protected
         */
        protected $_error_code;
            
        /**
         * Variable placeholder for determining if the current process is running. Only used when the blocking mode 
         * of the exec() call is set to run in the background.
         * @var boolean
         * @access protected
         */
        protected $_running;

        /**
         * Variable placeholder for containing the start timestamp of the exec() call.
         * @var integer
         * @access protected
         */
        protected $_start_time;

        /**
         * Variable placeholder for containing the end timestamp of the exec() call.
         * @var integer
         * @access protected
         */
        protected $_end_time;

        /**
         * Variable placeholder for wait period used before once again checking the buffer output for completion or failure
         * tokens.
         * @var integer
         * @access protected
         */
        protected $_callback_period_interval;

        /**
         * Variable placeholder for the default placeholder boundary token used within the failure, completion and error code
         * boundary markers.
         * @var string
         * @access protected
         */
        protected $_boundary;

        /**
         * Variable placeholder for the token used to detect failures in the exec() call.
         * @var string
         * @access protected
         */
        protected $_failure_boundary;

        /**
         * Variable placeholder for the token used to detect a completed exec call.
         * @var string
         * @access protected
         */
        protected $_completion_boundary;

        /**
         * Variable placeholder for the token to detect a error code in the exec call.
         * @var string
         * @access protected
         */
        protected $_error_code_boundary;

        /**
         * Variable placeholder for determining if the exec call should be put into set_time_limit(0).
         * @var boolean
         * @access protected
         */
        protected $_php_exec_infinite_timelimit;

        /**
         * Variable placeholder for an array of temporary files that need to be garbage collected at the end of the process.
         * @var array
         * @access protected
         */
        protected $_tmp_files;

        /**
         * Variable placeholder for determining if the temp files should be garbage collected on destruction of the ExecBuffer
         * object.
         * @var boolean
         * @access protected
         */
        protected $_gc_temp_files;

        /**
         * Variable placeholder for an array of completion callbacks that are fired when the process has completed.
         * @var array
         * @access protected
         */
        protected $_completion_callbacks;
        
        /**
         * The path to /dev/null
         * @var string
         */
        const DEV_NULL = '/dev/null';

        /**
         * The constant value for determining if the temp directory should be used to write the output buffer to.
         * @var integer
         */
        const TEMP = -1;

        /**
         * Static variable placeholder for determining if the current system is a windows system.
         * @var boolean
         * @access protected
         * @static
         */
        protected static $_is_windows = null;
        
        /**
         * The ExecBuffer constructor.
         *
         * @access public
         * @author Oliver Lillie
         * @param  string $exec_command_string The command to call through to exec()
         * @param  string $temp_directory The temp directory to use if any, otherwise null.
         * @param  boolean $php_exec_infinite_timelimit Determines if php should be put into set_time_limit(0) mode
         *  to endlessly run for the command, rather than timeout.
         */
        public function __construct($exec_command_string, $temp_directory=null, $php_exec_infinite_timelimit=true)
        {
            if(self::$_is_windows === null)
            {
                self::$_is_windows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            }

            $this->setTempDirectory($temp_directory);
            
            $this->_tmp_files = array();
            $this->setGarbageCollection(true);

            $this->_failure_tracking = true;
            $this->_blocking = true;
            $this->_output = self::TEMP;
            $this->_buffer = null;
            $this->_error_code = null;
            
            $this->_running = false;
            $this->_start_time = null;
            $this->_end_time = null;
            $this->_callback_period_interval = 1;
            
            $this->_php_exec_infinite_timelimit = $php_exec_infinite_timelimit;
            
            $this->_completion_callbacks = array();
            
            $id = uniqid(rand(99999, 999999).'-').'-'.md5(__FILE__);
            $this->_boundary = $id;
            $this->_error_code_boundary = '<e-'.$id.'>';
            $this->_failure_boundary = '<f-'.$id.'>';
            $this->_completion_boundary = '<c-'.$id.'>';
            
            $this->_command = $exec_command_string;
        }
        
        /**
         * Sets the temp directory.
         *
         * @access public
         * @author Oliver Lillie
         * @param  string $temp_directory The file path to the temp directory to use.
         * @return ExecBuffer Returns the current object.
         * @throws \InvalidArgumentException If the temp directory path is not a directory.
         * @throws \InvalidArgumentException If the temp directory path is not readable.
         * @throws \InvalidArgumentException If the temp directory path is not writable.
         */
        public function setTempDirectory($temp_directory=null)
        {
            if($temp_directory === null)
            {
                $temp_directory = sys_get_temp_dir();
            }
            if(is_dir($temp_directory) === false)
            {
                throw new \InvalidArgumentException('The temp directory does not exist or is not a directory.');
            }
            else if(is_readable($temp_directory) === false)
            {
                throw new \InvalidArgumentException('The temp directory is not readable.');
            }
            else if(is_writable($temp_directory) === false)
            {
                throw new \InvalidArgumentException('The temp directory is not writeable.');
            }
            $this->_temp_directory = $temp_directory;
            
            return $this;
        }

        /**
         * Sets the garabage collection status so that any temp files are or are not garabage collected on the 
         * destruct of ExecBuffer.
         *
         * @access public
         * @author Oliver Lillie
         * @param  boolean $gc True determines that the temp files are garbage collected. False means they are not.
         * @return ExecBuffer Returns the current object.
         * @throws \InvalidArgumentException If the $gc value is not a boolean.
         */
        public function setGarbageCollection($gc)
        {
            if(is_bool($gc) === false)
            {
                throw new \InvalidArgumentException('The $gc garabage collection value must be a boolean value.');
            }
            $this->_gc_temp_files = $gc;

            return $this;
        }
        
        /**
         * Destruct function automatically tidies up tmp files if garabge collection is enabled.
         *
         * @access public
         * @author Oliver Lillie
         * @return void
         */
        public function __destruct()
        {
            if($this->_gc_temp_files === true && empty($this->_tmp_files) === false)
            {
                foreach ($this->_tmp_files as $path)
                {
                    if(is_file($path) === true)
                    {
                        @unlink($path);
                    }
                }
            }
        }
        
        /**
         * Start the exec, essentially this is the call to php exec() whilst wrapping up the command string.
         *
         * @access public
         * @author Oliver Lillie
         * @param mixed $callback A function if provided is called when the exec() call is completed. Otherwise null.
         *  It should be noted that if a callback is supplied the resulting call to exec() is made blocking.
         * @return ExecBuffer Returns the current object.
         * @throws \InvalidArgumentException If a callback is supplied but not callable.
         */
        public function execute($callback=null)
        {
//          check the the callback is callable.
            if($callback !== null)
            {
                if(is_callable($callback) === false)
                {
                    throw new \InvalidArgumentException('The supplied callback is not callable.');
                }
                $this->setBlocking(true);
            }
            
//          get the execution string
            $this->_executed_command = $this->getExecString();

//          prevent the script timing out if the configuration allows.
            $previous_time_limit = ini_get('max_execution_time');
            if($this->_php_exec_infinite_timelimit === true)
            {
                set_time_limit(0);
            }

//          do the execution.
            $this->_running = true;
            $this->_start_time = time()+microtime(true);
            exec($this->_executed_command, $buffer, $err); // note error cannot be replied upon because of the output options.
            $buffer = implode(PHP_EOL, $buffer);
            $this->_error_code = $err;
            
//          reset the timelimit if required
            if($previous_time_limit > 0 && $this->_php_exec_infinite_timelimit === true)
            {
                set_time_limit($previous_time_limit);
            }
            
//          do we need to process output buffer
            if(empty($this->_output) === false)
            {
//              if the process is blocking, the run
                if($this->_blocking === true)
                {
                    if($callback !== null && is_callable($callback) === true)
                    {
                        call_user_func($callback, $this, null, null);
                    }
                    $this->_run($callback);
                }
            }
            
            if($this->_blocking === true)
            {
                $this->_end_time = time()+microtime(true);
            }
            
//          this is the final callback
//          this is the final callback to any function callback
            if($callback !== null && is_callable($callback) === true)
            {
                call_user_func($callback, $this, null, true);
            }

//          this is the callbacks to any completion callbacks.
            if($this->getBlocking() === true)
            {
                $this->_callCompletionCallbacks();
            }

            return $this;
        }

        /**
         * Calls the registered completion callbacks on completion of the exec() call.
         *
         * @access protected
         * @author Oliver Lillie
         * @return void
         */
        protected function _callCompletionCallbacks()
        {
            if(empty($this->_completion_callbacks) === false)
            {
                foreach ($this->_completion_callbacks as $callback)
                {
                    call_user_func($callback, $this);
                }
            }
        }

        /**
         * Registers an exec() call completion callback that will be called when the exec() call completes.
         * It should be noted that if the PHP script exits before the exec call is completed, then these callbacks are never fired.
         *
         * @access public
         * @author Oliver Lillie
         * @param  mixed $callback A callable callback function.
         * @return ExecBuffer Returns the current object.
         * @throws \InvalidArgumentException If a callback is supplied but not callable.
         */
        public function registerCompletionCallback($callback)
        {
            if(is_callable($callback) === true)
            {
                array_push($this->_completion_callbacks, $callback);
                return $this;
            }
            throw new \InvalidArgumentException('The completion callback was not callable.');
        }
        
        /**
         * Gets the current run time of the command execution.
         *
         * @access public
         * @author Oliver Lillie
         * @return float Returns the total time taken by the current process.
         * @throws \LogicException If the start time is not yet defined.
         */
        public function getRunTime()
        {
            if(empty($this->_start_time) === true)
            {
                throw new \LogicException('Unable to read runtime as command has not yet been executed.');
            }
            if(empty($this->_end_time) === false)
            {
                $end = $this->_end_time;
            }
            else
            {
                $end = time()+microtime(true);
            }
            
            return $end - $this->_start_time;
        }
        
        /**
         * If the process is made non-blocking, this runs the read/wait loop to determine the current status of the
         * ongoing process.
         * When the process is detected as being completed the completion callbacks are executed from this function.
         *
         * @access protected
         * @author Oliver Lillie
         * @param mixed $callback The callback should be a callable function.
         * @return void
         * @throws \InvalidArgumentException If the callback is supplied but not callable.
         */
        protected function _run($callback)
        {
            if($callback !== null && is_callable($callback) === false)
            {
                throw new \InvalidArgumentException('The ExecBuffer run callback was not callable.');
            }

            while($this->_running !== false)
            {
//              get the buffer regardless of wether or not there is a callback as it updates and 
//              checks for the completion of the command.
                $buffer = $this->getBuffer();

//              get the buffer to give to the
                if($callback !== null && is_callable($callback) === true)
                {
                    call_user_func($callback, $this, $buffer, false);
                }

//              if we have finished running the loop then break here.
                if($this->_running === false)
                {
                    if($this->getBlocking() === false)
                    {
                        $this->_callCompletionCallbacks();
                    }
                    break;
                }

//              still running so wait and then run again.
                $this->wait($this->_callback_period_interval);
            }
        }
        
        /**
         * Makes the read/wait loop wait.
         *
         * @access public
         * @author Oliver Lillie
         * @return ExecBuffer Returns the current object.
         */
        public function wait($seconds=1)
        {
            usleep($seconds*100000);
            return $this;
        }
        
        /**
         * Stops the read/wait loop from running.
         *
         * @access public
         * @author Oliver Lillie
         * @return ExecBuffer Returns the current object.
         */
        public function stop()
        {
            $this->_running = false;
            return $this;
        }
        
        /**
         * Returns the current buffer from the output of the exec() call.
         *
         * @access public
         * @author Oliver Lillie
         * @return string
         */
        public function getRawBuffer()
        {
             if(empty($this->_output) === false && is_file($this->_output) === true)
            {
                $buffer = file_get_contents($this->_output);
                if(empty($buffer) === false)
                {
                    $this->_buffer = $buffer;
                }
                $this->_detectCompletionAndEnd();
            }
            
            return $this->_buffer;
        }
        
        /**
         * Returns the expected buffer without the completion, error and failure boundaries.
         *
         * @access public
         * @author Oliver Lillie
         * @return string
         */
        public function getBuffer()
        {
            return rtrim(preg_replace(array('/'.$this->_failure_boundary.'/', '/'.$this->_completion_boundary.'/', '/'.$this->_error_code_boundary.(self::$_is_windows === false ? '([0-9]+)' : '').'/'), '', $this->getRawBuffer()));
        }
        
        /**
         * Returns the last line of the buffer.
         *
         * @access public
         * @author Oliver Lillie
         * @return string
         */
        public function getLastLine()
        {
            $buffer = $this->getBuffer();
            $lines = preg_split('/\r\n|\r|\n/', $buffer);
            return array_pop($lines);
        }
        
        /**
         * Returns the last split line of the buffer.
         *
         * @access public
         * @author Oliver Lillie
         * @return string
         */
        public function getLastSplit()
        {
            $buffer = $this->getBuffer();
            $splits = explode(PHP_EOL, $buffer);
            return array_pop($splits);
        }
        
        /**
         * Deletes the output buffer file. If garabage collection is disabled, then the file is not deleted
         *
         * @access public
         * @author Oliver Lillie
         * @return boolean If the file is deleted then true, otherwise false.
         */
        public function deleteOutputFile()
        {
            if(empty($this->_output) === false && is_file($this->_output) === true)
            {
                if($this->_gc_temp_files === true)
                {
                    @unlink($this->_output);
                }
                else
                {
                    return false;
                }
                $this->_output = null;
            }
            return true;
        }
        
        /**
         * Detects the failure boundary in the output.
         *
         * @access protected
         * @author Oliver Lillie
         * @param boolean $update_buffer If true the buffer is re-read from the output file.
         * @return boolean
         */
        protected function _detectFailureBoundaryInOutput($update_buffer=false)
        {
            if($this->_failure_tracking !== true)
            {
                return false;
            }
            
//          do we need to update the buffer?
            if($update_buffer === true)
            {
                $this->getBuffer();
            }
            
            return strpos($this->_buffer, $this->_failure_boundary) !== false;
        }
        
        /**
         * Returns any error code found in the output buffer.
         *
         * @access public
         * @author Oliver Lillie
         * @return mixed Returns null if no code is found, otherwise returns a string representation of the code.
         */
        public function getErrorCode()
        {
            return $this->_getErrorCodeInOutput();
        }
        
        /**
         * Returns if a failure boundary token is returned in the buffer output.
         *
         * @access public
         * @author Oliver Lillie
         * @return boolean Returns true if an error has been found, otherwise false.
         */
        public function hasError()
        {
            return $this->_detectFailureBoundaryInOutput(true);
        }
        
        /**
         * Returns a boolean to determine if the process has completed.
         *
         * @access public
         * @author Oliver Lillie
         * @return boolean Returns true if the process has been completed, otherwise false.
         */
        public function isCompleted()
        {
            return $this->_detectCompletionBoundaryInOutput(true);
        }
        
        /**
         * Detects any completion and or failure/error codes in the output. If the process is completed
         * it then stops the process and deletes the output file.
         *
         * @access public
         * @author Oliver Lillie
         * @return void
         */
        protected function _detectCompletionAndEnd()
        {
//          detect if the output has completed, and if it does
//          delete the output file and stop the loop.
            if($this->_detectCompletionBoundaryInOutput() === true)
            {
                $this->deleteOutputFile();
                $this->stop();
            }
            else if($this->_detectFailureBoundaryInOutput() === true)
            {
                $this->_error_code = $this->_getErrorCodeInOutput();
            }
        }
        
        /**
         * Tries to find an error code in the buffer output and optionaly re-reads the buffer output.
         *
         * @access public
         * @author Oliver Lillie
         * @param  boolean $update_buffer If true the buffer is re-read from the output file.
         * @return mixed If no error is found then null is return, otherwise a string representation of the error code is.
         */
        protected function _getErrorCodeInOutput($update_buffer=false)
        {
//          do we need to update the buffer?
            if($update_buffer === true)
            {
                $this->getBuffer();
            }
            
            if(preg_match('/'.$this->_error_code_boundary.'([0-9]+)/', $this->_buffer, $matches) > 0)
            {
                return $matches[1];
            }
            return null;
        }
        
        /**
         * Detects the completion boundary in the output.
         *
         * @access protected
         * @author Oliver Lillie
         * @param  boolean $update_buffer If true the buffer is re-read from the output file.
         * @return boolean Returns true if the completion token is detected in the buffer output.
         */
        protected function _detectCompletionBoundaryInOutput($update_buffer=false)
        {
//          do we need to update the buffer?
            if($update_buffer === true)
            {
                $this->getBuffer();
            }
            
            return strpos($this->_buffer, $this->_completion_boundary) !== false;
        }
        
        /**
         * Returns an augmented command string based on the classes options.
         *
         * @access public
         * @author Oliver Lillie
         * @return string
         */
        public function getExecString()
        {
            $command_string = $this->_command;
            
//          get the output append string
            $output = $this->getBufferOutput();
            if(empty($output) === false)
            {
                $output = ' > '.escapeshellarg($output);
            }
            
//          get the buffer divert string.
            $buffer_divert = '';
            if(empty($output) === false || empty($tracking) === false)
            {
                $buffer_divert = ' 2>&1 &';
            }
            
//          get the failure tracking boundaries
//          and get the completion boundary
            $completion_boundary_open = '';
            $completion_boundary_close = '';
            if($this->_failure_tracking === true)
            {
                $completion_boundary_open = '((';
                $completion_boundary_close = ' && echo '.$this->_escapeBoundaryInEcho($this->_completion_boundary).') || echo '.$this->_escapeBoundaryInEcho($this->_failure_boundary).' '.$this->_escapeBoundaryInEcho($this->_completion_boundary).' '.$this->_escapeBoundaryInEcho($this->_error_code_boundary).(self::$_is_windows === false ? '$?' : '').') 2>&1';
            }
            else
            {
                $completion_boundary_open = '(';
                $completion_boundary_close = ' && echo '.$this->_escapeBoundaryInEcho($this->_completion_boundary).') 2>&1';
            }
            
//          compile the final command and track
            return $completion_boundary_open.$command_string.$completion_boundary_close.$output.$buffer_divert;
        }

        /**
         * Escapes the boundaries, based on OS.
         *
         * @access public
         * @author Oliver Lillie
         * @param string $string The boundary token to escape.
         * @return string
         */
        protected function _escapeBoundaryInEcho($boundary)
        {
            return self::$_is_windows === true ? str_replace(array('<', '>'), array('^<', '^>'), $boundary) : escapeshellarg($boundary);
        }
        
        /**
         * Returns the executed command.
         *
         * @access public
         * @author Oliver Lillie
         * @return string The command string executed by the ExecBuffer exec() call.
         * @throws \LogicException if the exec() call has not yet been made.
         */
        public function getExecutedCommand()
        {
            if(empty($this->_executed_command) === true)
            {
                throw new \LogicException('The command has not yet been executed.');
            }
            
            return $this->_executed_command;
        }
        
        /**
         * Returns the original command that was passed through ExecBuffer.
         *
         * @access public
         * @author Oliver Lillie
         * @return string
         */
        public function getCommand()
        {
            return $this->_command;
        }
        
        /**
         * Sets wether or not we are to track failures of this exec() call.
         *
         * @access public
         * @author Oliver Lillie
         * @param boolean $enable_failure_tracking True means that failure tracking is enabled, false turns it off.
         * @return ExecBuffer Returns the current object.
         */
        public function setFailureTracking($enable_failure_tracking)
        {
            if(is_bool($enable_failure_tracking) === false && is_null($enable_failure_tracking) === false)
            {
                throw new \InvalidArgumentException('$enable_failure_tracking must be a boolean (or null) value.');
            }
            
            $this->_failure_tracking = $enable_failure_tracking;
            return $this;
        }
        
        /**
         * Gets the failure tracking status of the exec command.
         *
         * @access public
         * @author Oliver Lillie
         * @return boolean Or null.
         */
        public function getFailureTracking()
        {
            return $this->_failure_tracking;
        }
        
        /**
         * Sets the callback period wait interval used in the _run() read/wait loop process when the exec call is non blocking.
         *
         * @access public
         * @author Oliver Lillie
         * @param integer $callback_period_interval
         * @return ExecBuffer Returns the current object.
         * @throws \InvalidArgumentException If the callback period interval is not an integer.
         */
        public function setCallbackWaitInterval($callback_period_interval)
        {
            if(is_int($callback_period_interval) === false)
            {
                throw new \InvalidArgumentException('$callback_period_interval must be an integer.');
            }
            
            $this->_callback_period_interval = $callback_period_interval;
            return $this;
        }
        
        /**
         * Gets the callback period wait interval.
         *
         * @access public
         * @author Oliver Lillie
         * @return integer
         */
        public function getCallbackWaitInterval()
        {
            return $this->_callback_period_interval;
        }
        
        /**
         * Sets the exec calls blocking mode.
         *
         * @access public
         * @author Oliver Lillie
         * @param boolean $enable_blocking If true the exec() call is made blocking, false means it is non-blocking.
         * @return ExecBuffer Returns the current object.
         * @throws \InvalidArgumentException If the blocking value is not a boolean.
         */
        public function setBlocking($enable_blocking)
        {
            if(is_bool($enable_blocking) === false && is_null($enable_blocking) === false)
            {
                throw new \InvalidArgumentException('$enable_blocking must be a boolean (or null) value.');
            }
            
            $this->_blocking = $enable_blocking;
            return $this;
        }
        
        /**
         * Returns the exec calls blocking mode.
         *
         * @access public
         * @author Oliver Lillie
         * @return boolean
         */
        public function getBlocking()
        {
            return $this->_blocking;
        }
        
        /**
         * Returns the boundary placeholder for this exec call.
         *
         * @access public
         * @author Oliver Lillie
         * @return string
         */
        public function getBoundary()
        {
            return $this->_boundary;
        }
        
        /**
         * Sets the output of the exec call.
         *
         * @access public
         * @author Oliver Lillie
         * @param mixed $buffer_output Can be one of the following constants. ExecBuffer::DEV_NULL, ExecBuffer::TEMP, 
         *  a string will be interpretted as a file or null will output everything to sdout.
         * @return ExecBuffer Returns the current object.
         * @throws \InvalidArgumentException If the buffer output value is not null, ExecBuffer::DEV_NULL or ExecBuffer::TEMP
         *  and the directory path supplied is not a directory.
         * @throws \InvalidArgumentException If the buffer output value is not null, ExecBuffer::DEV_NULL or ExecBuffer::TEMP
         *  and the directory path supplied is not readable or not writable.
         */
        public function setBufferOutput($buffer_output)
        {
            if(in_array($buffer_output, array(null, self::DEV_NULL, self::TEMP)) === false)
            {
                $dir = dirname($buffer_output);
                if(is_dir($dir) === false)
                {
                    throw new \InvalidArgumentException('Buffer output parent directory "'.$dir.'" is not a directory.');
                }
                else if(is_readable($dir) === false || is_writeable($dir) === false)
                {
                    throw new \InvalidArgumentException('Buffer output parent directory "'.$dir.'" is not read-writable by the webserver.');
                }
            }
            
            $this->_output = $buffer_output;
            return $this;
        }
        
        /**
         * On first call it creates a temporary output file if set to null or ExecBuffer::TEMP, and the process is non-blocking.
         * Otherwise the current value help in ExecBuffer::$_output is returned.
         *
         * @access public
         * @author Oliver Lillie
         * @return string
         */
        public function getBufferOutput()
        {
            if($this->_output === null)
            {
                if($this->_blocking === false)
                {
                    return $this->_output = $this->generateTmpFile();
                }
            }
            else if($this->_output === self::TEMP)
            {
                return $this->_output = $this->generateTmpFile();
            }
            
            return $this->_output;
        }
        
        /**
         * Generate and create a temp file.
         *
         * @access public
         * @author Oliver Lillie
         * @return string
         */
        public function generateTmpFile($prefix='')
        {
            $tmp = tempnam($this->_temp_directory, 'phpvideotoolkit_'.$prefix);
            array_push($this->_tmp_files, $tmp);
            return $tmp;            
        }
        
    }
