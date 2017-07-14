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
     * This class is the base class for creating a specific ffmpeg command call.
     *
     * @author Oliver Lillie
     */
    class FfmpegProcess extends ProcessBuilder
    {
        /**
         * Variable placeholder for containing the ExecBuffer object.
         * @access protected
         * @var ExecBuffer
         */
        protected $_exec;

        /**
         * An array of commands to give to ffmpeg befor the -i input command.
         * @access protected
         * @var array
         */
        protected $_pre_input_commands;

        /**
         * An array of input media. 
         * @access protected
         * @var array
         */
        protected $_input;

        /**
         * Variable placeholder for the current output index.
         * @access protected
         * @var integer
         */
        protected $_output_index;

        /**
         * An array of commands to give to ffmpeg after the input -i commands are given.
         * @access protected
         * @var array
         */
        protected $_post_input_commands;

        /**
         * An array of output paths.
         * @access protected
         * @var array
         */
        protected $_output;

        /**
         * An array of commands to give to ffmpeg after the output is given.
         * @access protected
         * @var array
         */
        protected $_post_output_commands;

        /**
         * Variable placeholder for the progress handler, if any, that is attached to the process.
         * @access protected
         * @var ProgressHandlerAbstract
         */
        protected $_progress_handler;

        /**
         * Variable placeholder for a boolean value that determins if the commands supplied to this object have already
         * been combined together into a command string.
         * @access protected
         * @var boolean
         */
        protected $_combined;
        
        /**
         * Constructor
         *
         * @access public
         * @author Oliver Lillie
         * @param string $program The programme to call. Note this is not the path. If you wish to call ffmpeg/aconv you should jsut
         *  supply 'ffmpeg' and then set the aconv path as the ffmpeg configuration option in Config.   
         * @param Config $config The config object.
         */
        public function __construct($program, Config $config=null)
        {
            parent::__construct($program, $config);
            
            $this->_pre_input_commands = array();
            $this->_input = array();
            $this->_output_index = 0;
            $this->_post_input_commands = array();
            $this->_output = array();
            $this->_post_output_commands = array();
            $this->_exec = null;
            $this->_progress_handler = null;
            $this->_combined = false;
        }

        /**
         * Sets the output index to a specific index.
         *
         * @access public
         * @author Oliver Lillie
         * @param integer $index The index integer to set the output index to.
         * @return FfmpegProcess Returns the current object.
         * @throws \InvalidArgumentException If the $index is not an integer.
         */
        public function setOutputIndex($index)
        {
            if(is_int($index) === false)
            {
                throw new \InvalidArgumentException('The output index must be an integer.');
            }

            $this->_output_index = $index;
            return $this;
        }
        
        /**
         * Sets the input at the given index. If -1 is used, the input is shifted
         * onto the begining of the input array.
         *
         * @access public
         * @author Oliver Lillie
         * @param string $input The string file path to the input media.
         * @param integer $index The index to which the input is being added. If null then the input is just appended to the
         *  list of input media. If a positive index then it is set at the given index, it will overwrite anything already in that
         *  position. If -1 then the input is shifted onto the beinging of the input array.
         * @return FfmpegProcess Returns the current object.
         * @throws \InvalidArgumentException If the input path does not exist.
         * @throws \InvalidArgumentException If the $index is not an integer.
         */
        public function setInputPath($input, $index=null)
        {
            if(file_exists($input) === false || is_file($input) === false)
            {
                throw new \InvalidArgumentException('The input supplied `'.$input.'` is not a file or it does not exist.');
            }

            if($index === null)
            {
                array_push($this->_input, $input);
            }
            else if(is_int($index) === false)
            {
                throw new \InvalidArgumentException('The input index must be an integer.');
            }
            else if($index === -1)
            {
                array_unshift($this->_input, $input);
            }
            else
            {
                $this->_input[$index] = $input;
            }

            return $this;
        }

        /**
         * Gets the input path at the given index.
         *
         * @access public
         * @author Oliver Lillie
         * @param integer $index The index of which to return the input for.
         * @return string Returns the input from the requested index if exists.
         * @throws \InvalidArgumentException If the input index is not an integer.
         * @throws \LogicException If the input at the requested index does not exist.
         */
        public function getInputPath($index=0)
        {
            if(is_int($index) === false)
            {
                throw new \InvalidArgumentException('The input index must be an integer.');
            }

            if(isset($this->_input[$index]) === true)
            {
                return $this->_input[$index];
            }
            
            throw new \LogicException('No input existed for given index `'.$index.'`');
        }
        
        /**
         * Gets ALL the input given to the process.
         *
         * @access public
         * @author Oliver Lillie
         * @return array Returns an array of all the current input paths.
         */
        public function getAllInput()
        {
            return $this->_input;
        }
        
        /**
         * Gets ALL the output given to the process.
         *
         * @access public
         * @author Oliver Lillie
         * @return array Returns an array of all the current output paths.
         */
        public function getAllOutput()
        {
            return $this->_output;
        }
        
        /**
         * Sets the output at the current output index.
         *
         * @access public
         * @author Oliver Lillie
         * @param string $output The path to where the output media is to be saved to from ffmpeg.
         * @return FfmpegProcess Returns the current object.
         */
        public function setOutputPath($output)
        {
            $this->_output[$this->_output_index] = $output;

            return $this;
        }

        /**
         * Returns the current number of output files that the FfmpegProcess object contains.
         *
         * @access public
         * @author: Oliver Lillie
         * @return integer
         */
        public function getOutputCount()
        {
            return count($this->_output);
        }

        /**
         * Gets the output path at the index requested, or no index is request gets the output from the current index.
         *
         * @access public
         * @author: Oliver Lillie
         * @param integer $index The index of the output to return. If left null defaults to the currently incremented
         *  index.
         * @return mixed If the index has not been set and the output does not exist then null is returned, if the output
         *  does not exist but index has been set then a LogicException is thrown. If the output does exist then the string 
         *  path is returned.
         * @throws \InvalidArgumentException If the output index requested is not null and not an integer.
         * @throws \LogicException If the output does not exist and index has been set.
         */
        public function getOutputPath($index=null)
        {
            $throw_exception_if_not_exist = false;
            if($index !== null && is_int($index) === false)
            {
                throw new \InvalidArgumentException('The output path index must be an integer.');
            }
            else if($index === null)
            {
                $index = $this->_output_index;
            }
            else
            {
                $throw_exception_if_not_exist = true;
            }

            if($throw_exception_if_not_exist === true && isset($this->_output[$index]) === false)
            {
                throw new \LogicException('The requested output index has not been set.');
            }

            return isset($this->_output[$index]) === true ? $this->_output[$index] : null;
        }

        /**
         * Adds a command to be bundled into command line call to be 
         * added to the command line call before the input file is added.
         *
         * @access public
         * @author: Oliver Lillie
         * @param string $command The command to add.
         * @param mixed $argument Any optional arguments to add. If none, false should be given.
         * @param boolean $allow_command_repetition If this command can only be added once then set this to true to prevent
         *  it from being added again.
         * @return FfmpegProcess Returns the current object.
         */
        public function addPreInputCommand($command, $argument=false, $allow_command_repetition=false)
        {
            $this->_add($this->_pre_input_commands, $command, $argument, $allow_command_repetition);

            return $this;
        }

        /**
         * Adds a command to be bundled into command line call to be 
         * added to the command line call after the input file is added.
         *
         * @access public
         * @author: Oliver Lillie
         * @param string $command The command to add.
         * @param mixed $argument Any optional arguments to add. If none, false should be given.
         * @param boolean $allow_command_repetition If this command can only be added once then set this to true to prevent
         *  it from being added again.
         * @return FfmpegProcess Returns the current object.
         */
        public function addCommand($command, $argument=false, $allow_command_repetition=false)
        {
            if(isset($this->_post_input_commands[$this->_output_index]) === false)
            {
                $this->_post_input_commands[$this->_output_index] = array();
            }
            $this->_add($this->_post_input_commands[$this->_output_index], $command, $argument, $allow_command_repetition);

            return $this;
        }

        /**
         * Adds a command to be bundled into command line call to be 
         * added to the command line call after the ouput file(s) is added.
         *
         * @access public
         * @author: Oliver Lillie
         * @param string $command The command to add.
         * @param mixed $argument Any optional arguments to add. If none, false should be given.
         * @param boolean $allow_command_repetition If this command can only be added once then set this to true to prevent
         *  it from being added again.
         * @return FfmpegProcess Returns the current object.
         */
        public function addPostOutputCommand($command, $argument=false, $allow_command_repetition=false)
        {
            $this->_add($this->_post_output_commands, $command, $argument, $allow_command_repetition);
            return $this;
        }

        /**
         * Removes a command from the pre input command list.
         *
         * @access public
         * @author: Oliver Lillie
         * @param string $command The command to add.
         * @param mixed $argument Any optional arguments to add. If none, false should be given.
         * @return FfmpegProcess Returns the current object.
         */
        public function removePreInputCommand($command, $argument=false)
        {
            $this->_remove($this->_pre_input_commands, $command, $argument);
            return $this;
        }

        /**
         * Removes a command from the post input command list.
         *
         * @access public
         * @author: Oliver Lillie
         * @param string $command The command to add.
         * @param mixed $argument Any optional arguments to add. If none, false should be given.
         * @return FfmpegProcess Returns the current object.
         */
        public function removeCommand($command, $argument=false)
        {
            $this->_remove($this->_post_input_commands, $command, $argument);
            return $this;
        }

        /**
         * Removes a command from the post output command list.
         *
         * @access public
         * @author: Oliver Lillie
         * @param string $command The command to add.
         * @param mixed $argument Any optional arguments to add. If none, false should be given.
         * @return FfmpegProcess Returns the current object.
         */
        public function removePostOutputCommand($command, $argument=false)
        {
            $this->_remove($this->_post_output_commands, $command, $argument);
            return $this;
        }

        /**
         * Determines if the the command exits in the pre-input commands.
         *
         * @access public
         * @author: Oliver Lillie
         * @param string $command
         * @return mixed boolean If not found then false is returned. If found then the argument for that command (if any) is returned. Otherwise false is returned.
         */
        public function hasPreInputCommand($command)
        {
            return isset($this->_pre_input_commands[$command]) === true ? ($this->_pre_input_commands[$command] === false ? true : $this->_pre_input_commands[$command]): false;
        }
        
        /**
         * Returns a pre input command.
         *
         * @access public
         * @author: Oliver Lillie
         * @param string $command
         * @return mixed If the command does not exist then false is returned, otherwise the command argument (if any is returned).
         */
        public function getPreInputCommand($command)
        {
            if($this->hasPreInputCommand($command) === false)
            {
                return false;
            }
            
            return $this->_pre_input_commands[$command];
        }
        
        /**
         * Determines if the the command exits.
         *
         * @access public
         * @author: Oliver Lillie
         * @param string $command
         * @param integer $index The index of the output to return. If left null defaults to the currently incremented
         *  index.
         * @return mixed If the command does not exist then false is returned, otherwise the command argument (if any is returned).
         */
        public function hasCommand($command, $index=null)
        {
            $index = $index === null ? $this->_output_index : $index;
            if(isset($this->_post_input_commands[$index]) === false)
            {
                return false;
            }
            return isset($this->_post_input_commands[$index][$command]) === true ? ($this->_post_input_commands[$index][$command] === false ? true : $this->_post_input_commands[$index][$command]): false;
        }
        
        /**
         * Returns an output command.
         *
         * @access public
         * @author: Oliver Lillie
         * @param string $command
         * @param integer $index The index of the output to return. If left null defaults to the currently incremented
         *  index.
         * @return mixed If the command does not exist then false is returned, otherwise the command argument (if any is returned).
         */
        public function getCommand($command, $index=null)
        {
            $index = $index === null ? $this->_output_index : $index;
            if($this->hasCommand($command, $index) === false)
            {
                return false;
            }
            
            return $this->_post_input_commands[$index][$command];
        }
        
        /**
         * Determines if the post output command exits.
         *
         * @access public
         * @author: Oliver Lillie
         * @param string $command
         * @return mixed If the command does not exist then false is returned, otherwise the command argument (if any is returned).
         */
        public function hasPostOutputCommand($command)
        {
            return isset($this->_post_output_commands[$command]) === true ? ($this->_post_output_commands[$command] === false ? true : $this->_post_output_commands[$command]): false;
        }
        
        /**
         * Returns a post output command.
         *
         * @access public
         * @author: Oliver Lillie
         * @param string $command
         * @return mixed If the command does not exist then false is returned, otherwise the command argument (if any is returned).
         */
        public function getPostOutputCommand($command)
        {
            if($this->hasPostOutputCommand($command) === false)
            {
                return false;
            }
            
            return $this->_post_output_commands[$command];
        }
        
        /**
         * Combines the commands stored into a string internaly.
         *
         * @access protected
         * @author: Oliver Lillie
         * @return void
         */
        protected function _combineCommands()
        {
            if($this->_combined === true)
            {
                return;
            }
            $this->_combined = true;

            $args = $this->_arguments;
            $this->_arguments = array();
            
//          add the pre input commands
            if(empty($this->_pre_input_commands) === false)
            {
                $this->addCommands($this->_pre_input_commands);
            }
            
//          add in the input
            if(empty($this->_input) === false)
            {
                foreach ($this->_input as $input)
                {
                    $this->add('-i')
                         ->add($input);
                }
            }

//          build the multiple post input  and output path commands
            for($i=0; $i<=$this->_output_index; $i++)
            {
//              build the post input commands
                if(isset($this->_post_input_commands[$i]) === true && empty($this->_post_input_commands[$i]) === false)
                {
                    $this->addCommands($this->_post_input_commands[$i]);
                }
                if(empty($args) === false)
                {
                    $this->_arguments = array_merge($this->_arguments, $args);
                }
            
//              add in the output
                if(isset($this->_output[$i]) === true && empty($this->_output[$i]) === false)
                {
                    $this->add($this->_output[$i]);
                }
            }
            
//          build the post output commands
            if(empty($this->_post_output_commands) === false)
            {
                $this->addCommands($this->_post_output_commands);
            }
        }
        
        /**
         * Returns the command string to be executed.
         *
         * @access public
         * @author Oliver Lillie
         * @return string
         */
        public function getCommandString()
        {
            $this->_combineCommands();
            return parent::getCommandString();
        }
        
        /**
         * Get the ExecBuffer object by combining the commands the creating in the buffer.
         *
         * @access protected
         * @author Oliver Lillie
         * @return ExecBuffer
         */
        protected function _getExecBuffer()
        {
            $this->_combineCommands();
            return parent::getExecBuffer();
        }
        
        /**
         * Get the initialised ExecBuffer object.
         *
         * @access public
         * @author Oliver Lillie
         * @return ExecBuffer
         */
        public function &getExecBuffer()
        {
            if(empty($this->_exec) === true)
            {
                $this->_exec = $this->_getExecBuffer();
            }
            return $this->_exec;
        }
        
        /**
         * Execute the buffer command.
         *
         * @access public
         * @author Oliver Lillie
         * @return self
         */
        public function execute()
        {
            $this->getExecBuffer()
                 ->setBlocking(true)
                 ->execute();
            
            return $this;
        }
        
        /**
         * Protected private function for calling functions from the ExecBuffer.
         *
         * @access protected
         * @author Oliver Lillie
         * @param string $function The name of the function to call on the ExecBuffer object.
         * @param array $arguments An array of arguments to supply to the called function.
         * @return mixed
         */
        protected function _callExecBufferFunction($function, $arguments=array())
        {
//          if no exec has been created then it has not completed.
            if(empty($this->_exec) === true)
            {
                return false;
            }
            
            if(is_callable(array($this->_exec, $function)) === false)
            {
                throw new FfmpegProcessException('This function is not callable within ExecBuffer.', $this->_exec, $this);
            }
            
            return call_user_func_array(array($this->_exec, $function), $arguments);
        }
        
        /**
         * Returns any "[xxx @ xxxxx] message" messages set in the buffer by FFmpeg.
         *
         * @access public
         * @author Oliver Lillie
         * @return array Returns an array of strings if any messages are present.
         */
        public function getMessages()
        {
            $messages = array();
            $buffer = $this->getBuffer();
            if(empty($buffer) === false)
            {
                // 0x7f9db9065a00
                if(preg_match_all('/\[([a-zA-Z0-9]+) @ (0x[a-z0-9]+)\] (.*)/', $buffer, $matches) > 0)
                {
                    foreach ($matches[1] as $key=>$match)
                    {
                        if(isset($messages[$match]) === false)
                        {
                            $messages[$match] = array();
                        }
                        if(isset($messages[$match][$matches[2][$key]]) === false)
                        {
                            $messages[$match][$matches[2][$key]] = array();
                        }
                        array_push($messages[$match][$matches[2][$key]], $matches[3][$key]);
                    }
                }
            }
            return $messages;
        }
        
        /**
         * Returns the current (or if called after isCompleted() returns true, the completed)
         * run time of the exec function.
         *
         * @access public
         * @author Oliver Lillie
         * @return mixed
         */
        public function getRunTime()
        {
            return $this->_callExecBufferFunction('getRunTime');
        }
        
        /**
         * Returns the buffers command or executed command.
         *
         * @access public
         * @author Oliver Lillie
         * @see ExecBuffer::getExecutedCommand
         * @param boolean $raw If true then the raw command is returned from the buffer, otherwise
         *  the original command is returned.
         * @return mixed
         */
        public function getExecutedCommand($raw=false)
        {
            return $this->_callExecBufferFunction($raw === false ? 'getCommand' : 'getExecutedCommand');
        }
        
        /**
         * Returns the filtered buffer output of ExecBuffer.
         *
         * @access public
         * @author Oliver Lillie
         * @see ExecBuffer::getBuffer
         * @param boolean $raw If true then the raw command is returned from the buffer, otherwise
         *  the original command is returned.
         * @return mixed
         */
        public function getBuffer($raw=false)
        {
            return $this->_callExecBufferFunction($raw === false ? 'getBuffer' : 'getRawBuffer');
        }
        
        /**
         * Returns the last line from the buffer output of ExecBuffer.
         *
         * @access public
         * @author Oliver Lillie
         * @see ExecBuffer::getLastLine
         * @return mixed
         */
        public function getLastLine()
        {
            return $this->_callExecBufferFunction('getLastLine');
        }
        
        /**
         * Returns the last split from the buffer output of ExecBuffer.
         *
         * @access public
         * @author Oliver Lillie
         * @see ExecBuffer::getLastSplit
         * @return mixed
         */
        public function getLastSplit()
        {
            return $this->_callExecBufferFunction('getLastSplit');
        }
        
        /**
         * Returns the error code encountered by the ExecBuffer.
         *
         * @access public
         * @author Oliver Lillie
         * @see ExecBuffer::getErrorCode
         * @return mixed
         */
        public function getErrorCode()
        {
            return $this->_callExecBufferFunction('getErrorCode');
        }
        
        /**
         * Returns a boolean value determining if the process has encountered an error.
         * Typically if this returns true, it also means the process has completed.
         *
         * @access public
         * @author Oliver Lillie
         * @see ExecBuffer::hasError
         * @param boolean $delete_output_on_error If true and an error has been encountered
         *  and the output has been set and the output exists, then the output is deleted.
         * @return boolean
         */
        public function hasError($delete_output_on_error=true)
        {
            $has_error = $this->_callExecBufferFunction('hasError');
            
//          if we have an error and we want to delete any output on the error
            if($delete_output_on_error === true && $has_error === true)
            {
                foreach ($this->_output as $output)
                {
                    if(empty($output) === false && is_file($output) === true)
                    {
                        @unlink($output);
                    }
                }
            }
            
            return $has_error;
        }
        
        /**
         * Returns a boolean value determining if the process has completed.
         *
         * @access public
         * @author Oliver Lillie
         * @see ExecBuffer::isCompleted
         * @return boolean Returns true if the process is completed, otherwise false.
         */
        public function isCompleted()
        {
            return $this->_callExecBufferFunction('isCompleted');
        }
        
        /**
         * Returns the file name of the exec buffer output.
         *
         * @access public
         * @author Oliver Lillie
         * @see ExecBuffer::getBufferOutput
         * @return string
         */
        public function getBufferOutput()
        {
            return $this->_callExecBufferFunction('getBufferOutput');
        }
        
        /**
         * Returns a string value of a portable identifier used in conjunction with ProgressHandlerPortable.
         * WARNING. If this function is called it automatically disables the garbage collection of the ExceBuffer.
         * WARNING. This function should not be called directly. Please use Media::getPortableId instead.
         *
         * @access public
         * @author Oliver Lillie
         * @return string
         */
        public function getPortableId()
        {
            if($this->_callExecBufferFunction('getBlocking') === true)
            {
                throw new \LogicException('It is not possible to get a portable id as the exec process has been made blocking. To get a portable id make the process unblocking or call getPortableId() before the save occurs.');
            }
            $trace = debug_backtrace();
            if(isset($trace[1]) === false || $trace[1]['function'] !== 'getPortableId' || $trace[1]['class'] !== 'PHPVideoToolkit\Media')
            {
                throw new \LogicException('Please call getPortableId from the media object rather than the process object, i.e. $video->getPortableId();');
            }

            $this->_exec->setGarbageCollection(false);
            
            $output = $this->getBufferOutput();
            return substr($output, strrpos($output, 'phpvideotoolkit_')+16).'.'.$this->_callExecBufferFunction('getBoundary').'.'.time();
        }
    }
