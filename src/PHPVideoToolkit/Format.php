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
     * This is the base format class that is extended to audio, image and video formats.
     * It provides functions that are shared by all media types.
     * 
     * @author Oliver Lillie
     */
    class Format extends FfmpegParser
    {
        /**
         * [$_output_renamed description]
         * @var [type]
         * @access private
         */
        protected $_format;

        /**
         * [$_output_renamed description]
         * @var [type]
         * @access private
         */
        protected $_type;

        /**
         * [$_output_renamed description]
         * @var [type]
         * @access private
         */
        protected $_media_object;

        /**
         * [$_output_renamed description]
         * @var [type]
         * @access private
         */
        protected $_format_to_command;

        /**
         * [$_output_renamed description]
         * @var [type]
         * @access private
         */
        protected $_additional_commands;

        /**
         * [$_output_renamed description]
         * @var [type]
         * @access private
         */
        protected $_removed_commands;

        /**
         * [$_output_renamed description]
         * @var [type]
         * @access private
         */
        const INPUT = 'input';

        /**
         * [$_output_renamed description]
         * @var [type]
         * @access private
         */
        const OUTPUT = 'output';

        /**
         * [$_output_renamed description]
         * @var [type]
         * @access private
         */
        const STRICTNESS_VERY = 'very';
        const STRICTNESS_STRICT = 'strict';
        const STRICTNESS_NORMAL = 'normal';
        const STRICTNESS_UNOFFICIAL = 'unofficial';
        const STRICTNESS_EXPERIMENTAL = 'experimental';

        /**
         * Constructor
         *
         * @access public
         * @author: Oliver Lillie
         * @param  constant $input_output_type Either Format::INPUT or Format::OUTPUT. Defaults to OUTPUT. It determines the format
         *  mode used to set various commands in the final ffmpeg exec call.
         * @param  Config $config The config object.
         * @throws \InvalidArgumentException If the $input_output_type is not valid.
         */
        public function __construct($input_output_type=Format::OUTPUT, Config $config=null)
        {
            parent::__construct($config);
            
            $this->setType($input_output_type);
            
            $this->_additional_commands = array();
            $this->_removed_commands = array();
            
            $this->_media_object = null;
            
            $this->_format = array(
                'quality' => null,
                'format' => null,
                'strictness' => null,
                'preset_options_file' => null,
                'threads' => null,
                'threads_indexed' => null,
            );

            $ffmpeg = new FfmpegParser($config);
            $available_commands = $ffmpeg->getCommands();
            if(isset($available_commands['crf']))
            {
                $quality_command = '-crf <setting>';
            }
            else if(isset($available_commands['q']) === true)
            {
                $quality_command = '-q <setting>';
            }
            else 
            {
                $quality_command = '-qscale <setting>';
            }

            $this->_format_to_command = array(
                'quality' => $quality_command,
                'format'  => '-f <setting>',
                'strictness'  => '-strict <setting>',
                'preset_options_file'  => '-fpre <setting>',
                'threads'  => '-threads <setting>',
                'threads_indexed'  => '-threads:<index> <setting>',
            );
            
//          add default input/output commands
            if($input_output_type === self::OUTPUT)
            {
                if($this->_config->set_default_output_format === true)
                {
                    $this->setThreads(1)
                         ->setStrictness('experimental')
                         ->setQualityVsStreamabilityBalanceRatio(4);
                }
            }
            else if($input_output_type === self::INPUT)
            {
            }
            else
            {
                throw new \InvalidArgumentException('Unrecognised input/output type "'.$input_output_type.'" set in \\PHPVideoToolkit\\Format::__construct');
            }
            
        }

        /**
         * Gets a default format for the related path.
         * If a default format is not found then the fallback_format_class is used.
         *
         * @access public
         * @static
         * @author Oliver Lillie
         * @param string  $path The file path to get the format for.
         * @param  Config $config The config object.
         * @param string  $fallback_format_class The fallback class to use of the format for the given path cannot be automatically determined.
         *  If null is given then a RuntimeException is thrown.
         * @param string  $type
         * @return Format Returns an object extended from the PHPVideToolkit\Format class.
         * @throws \LogicException
         * @throws \RuntimeException
         * @throws \InvalidArgumentException
         * @internal param constant $input_output_type Either Format::INPUT or Format::OUTPUT. Defaults to OUTPUT. It determines the format
         *  mode used to set various commands in the final ffmpeg exec call.
         */
        public static function getFormatFor($path, $config, $fallback_format_class='Format', $type=Format::OUTPUT)
        {
            if(in_array($type, array(Format::OUTPUT, Format::INPUT)) === false)
            {
                throw new \InvalidArgumentException('Unrecognised format type "'.$type.'".');
            }
            
            $format = null;
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if(empty($ext) === false)
            {
                $format = Extensions::toBestGuessFormat($ext);
            }
            
//          check the requested class exists
            $class_name = '\\PHPVideoToolkit\\'.$fallback_format_class.(empty($format) === false ? '_'.ucfirst(strtolower($format)) : '');
            if(class_exists($class_name) === false)
            {
                if($fallback_format_class === null)
                {
                    throw new \RuntimeException('It was not possible to generate the format class for `'.$path.'` and a fallback class was not given.');
                }
                $requested_class_name = $class_name;
                $class_name = '\\PHPVideoToolkit\\'.$fallback_format_class;
                if(class_exists($class_name) === false)
                {
                    throw new \InvalidArgumentException('Requested default format class does not exist, "'.($requested_class_name === $class_name ? $class_name : $requested_class_name.'" and "'.$class_name.'"').'".');
                }
            }

//          check that it extends from the base Format class.
            if($class_name !== '\\PHPVideoToolkit\\Format' && is_subclass_of($class_name, '\\PHPVideoToolkit\\Format') === false)
            {
                throw new \LogicException('The class "'.$class_name.'" is not a subclass of \\PHPVideoToolkit\\Format.');
            }
            
            return new $class_name($type, $config);
        }
        
        /**
         * Sets a filter onto the format.
         *
         * @access public
         * @author: Oliver Lillie
         * @param  string $format_key The filter type, ie audio, video etc.
         * @param  FilterAbstract $filter The filter object to apply to the output format.
         * @return integer Returns the position of the filter in the filters array.
         * @throws \InvalidArgumentException If the specified format key does not exist.
         */
        protected function _setFilter($format_key, FilterAbstract $filter)
        {
            if(isset($this->_format[$format_key]) === false)
            {
                throw new \InvalidArgumentException('Unknown format key uncountered when setting a filter.');
            }
            
            if($this->_format[$format_key] === null)
            {
                $this->_format[$format_key] = array();
            }
            
            return array_push($this->_format[$format_key], $filter)-1;
        }
        
        /**
         * Returns the format options array. The format options are the key name => value pairs.
         *
         * @access public
         * @author Oliver Lillie
         * @return array
         */
        public function getFormatOptions()
        {
            return $this->_format;
        }
        
        /**
         * Sets the PHPVideoToolkit\Media object into the format so that format object can modify the Media object.     
         *
         * @access public
         * @author Oliver Lillie
         * @param Media $media
         * @return Format Returns the current object.
         */
        public function setMedia(Media $media)
        {
            $this->_media_object = $media;
            return $this;
        }
        
        /**
         * Blocks a particular setting from being set if it is attempted to be set on an input format.
         * This is usefull from preventing the input formats from adding junk commands to the ffmpeg call that would result in 
         * an error.
         *
         * @access public
         * @author Oliver Lillie
         * @param string $setting_name The setting name being set.
         * @return void
         * @throws \LogicException If the format type is INPUT.
         */
        protected function _blockSetOnInputFormat($setting_name)
        {
            if($this->_type === Format::INPUT)
            {
                $backtrace = debug_backtrace();
                throw new \LogicException('The '.$setting_name.' cannot be set on an input \\'.get_class($backtrace[1]['object']).'::'.$backtrace[1]['function'].'.');
            }
        }
        
        /**
         * Base function extended by the child format classes. Checks to see if the media option has been set yet
         * and if not an exception is thrown.
         *
         * @access public
         * @author Oliver Lillie
         * @return Format Returns the current object.
         * @throws \LogicException If the media object has not yet been set into the current format object.
         */
        public function updateFormatOptions(&$save_path, $overwrite)
        {
            if(empty($this->_media_object) === true)
            {
                throw new \LogicException('Unable to update format options as a Media object has not been set through '.get_class($this).'::setMedia');
            }
            
            return $this;
        }
        
        /**
         * Builds a returnable command string for the give options and additional commands.
         *
         * @access public
         * @author Oliver Lillie
         * @return string
         */
        public function getCommandString()
        {
            $commands = $this->getCommandsHash();
            
            $command_string = '';
            if(empty($commands) === false)
            {
                array_walk($commands, function(&$value, $key)
                {
                    $value = $key.' '.$value;
                });
                $command_string = implode(' ', $commands);
            }
            
            return $command_string;
        }
        
        /**
         * Builds a returnable command array hash for the give options and additional commands.
         * The array is in ffmpeg command => argument key value pairs.
         *
         * @access public
         * @author Oliver Lillie
         * @return array
         */
        public function getCommandsHash()
        {
            $commands = array();
            
            $mapped_commands = array_values($this->_mapFormatToCommands());
            $additional_commands = $this->_getAdditionalCommands();
            $merged_commands = array_merge($commands, $mapped_commands, $additional_commands);
            
            $commands = array();
            if(empty($merged_commands) === false)
            {
                foreach ($merged_commands as $key=>$command)
                {
                    if(is_array($command) === true)
                    {
                        array_splice($merged_commands, $key, 1, $command);
                    }
                }
                foreach ($merged_commands as $command)
                {
                    if(preg_match('/^([^\s]+)\s+(.*)/', $command, $matches) > 0)
                    {
//                      check to see if we have the special "audio/video filters".
//                      if so then they must be grouped together in order to be sent.
                        // TODO decouple this into their own class
                        if($matches[1] === '-af' || $matches[1] === '-vf')
                        {
                            if(isset($commands[$matches[1]]) === false)
                            {
                                $commands[$matches[1]] = array();
                            }
                            array_push($commands[$matches[1]], $matches[2]);
                        }
                        else
                        {
                            $commands[$matches[1]] = trim($matches[2]);
                        }
                    }
                    else
                    {
                        $commands[$command] = '';
                    }
                }
            }
            
//          post process the special cases for filters
            // TODO decouple into own classes.
            if(isset($commands['-vf']) === true)
            {
                $commands['-vf'] = implode(',', $commands['-vf']);
            }
            if(isset($commands['-af']) === true)
            {
                $commands['-af'] = implode(',', $commands['-af']);
            }
            return $commands;
        }
        
        /**
         * Builds the additional commands.
         *
         * @access public
         * @author Oliver Lillie
         * @return array Returns an array of additional commands that have been compiled into strings.
         */
        protected function _getAdditionalCommands()
        {
            $commands = array();
            
            if(empty($this->_additional_commands) === false)
            {
                foreach ($this->_additional_commands as $option => $value)
                {
                    array_push($commands, $option.' '.$value);
                }
            }
            
            return $commands;
        }
        
        /**
         * Maps the supplied format options to those in the Format->_format_to_command array.
         *
         * @access protected
         * @author Oliver Lillie
         * @return array
         * @throws \UnexpectedValueException if an unexpected command options is encountered.
         */
        protected function _mapFormatToCommands()
        {
            $options = array();

            foreach ($this->_format as $option => $value)
            {
//              if the value is explicitly null or false we ignore it as it has not been set.
//              if the value is to be set but not be ignored then it should be set as an empty string, ie '';
                if($value === null || $value === false)
                {
                    continue;
                }
                
                if(isset($this->_format_to_command[$option]) === false)
                {
                    throw new \UnexpectedValueException('Unable to map format option to command option as the command option does not exist in the map.');
                }
                
//              get the full command option string
                $full_command = $this->_format_to_command[$option];
                if(empty($full_command) === true)
                {
                    continue;
                }
                
//              if the command is an array, that means it has differing options depending on whether or not
//              this is an input or output format.
                if(is_array($full_command) === true)
                {
                    $full_command = $full_command[$this->_type];
                }
                
//              now just the main command so we can ignore it if found in the additional supplied commands 
//              list of the list of commands to ignore.
                preg_match('/^([^\s]+)/', $full_command, $matches);
                $command_name = $matches[1];

//              if we've been set this command as an additional command, the additional command takes precedent
                if(isset($this->_additional_commands[$command_name]) === true)
                {
                    continue;
                }
                
//              do we have to "remove" / ignore any commands?
                if(isset($this->_removed_commands[$command_name]) === true)
                {
                    continue;
                }
                
//              otherwise if the value is an array, that means we have multiple options to replace into the command
                if(is_array($value) === true)
                {
//                  if we have an index then we have multiple singles of this command.
                    if(strpos($full_command, '<index>') !== false)
                    {
                        $commands = array();
                        foreach ($value as $k1=>$v1) 
                        {
                            array_push($commands, str_replace(array('<index>', '<setting>'), array((string) $k1, (string) $v1), $full_command));
                        }
                        $command = $commands;
                    }
                    else
                    {
                        $find = array_keys($value);
                        array_walk($find, function(&$value, $key)
                        {
                            $value = '<'.$value.'>';
                        });
                        $command = str_replace($find, $value, $full_command);
                    }
                }
//              otherwise, it's jsut a <setting> that is to be replaced
                else
                {
                    $command = str_replace('<setting>', $value, $full_command);
                }
                
                $options[$option] = $command;
            }

            return $options;
        }
        
        /**
         * Adds additional commands that will be added to the formatted command string
         * that is returned from the format object. Any command options added here take
         * precendence over those set in the Format->_format array
         *
         * @access public
         * @author Oliver Lillie
         * @param string $option 
         * @param string $arg 
         * @return void
         */
        public function addCommand($option, $arg)
        {
            $this->_additional_commands[$option] = $arg;
        }
        
        /**
         * Sets the format type, either input or output
         *
         * @access public
         * @author Oliver Lillie
         * @param  constant $type Either Format::INPUT or Format::OUTPUT. Defaults to OUTPUT. It determines the format
         *  mode used to set various commands in the final ffmpeg exec call.
         * @return Format Returns the current object.
         * @throws \InvalidArgumentException If the type is not a valid type.
         */
        public function setType($type)
        {
//          validate input
            if(in_array($type, array(Format::INPUT, Format::OUTPUT)) === true)
            {
                $this->_type = $type;
                return $this;
            }
            
            throw new \InvalidArgumentException('Unrecognised format "'.$format.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setFormat');
        }
        
        /**
         * A preset file contains a sequence of option=value pairs, one for each line, specifying a sequence of options 
         * which would be awkward to specify on the command line. Lines starting with the hash (’#’) character are ignored 
         * and are used to provide comments. Check the ‘presets’ directory in the FFmpeg source tree for examples.
         *
         * @access public
         * @author Oliver Lillie
         * @link http://ffmpeg.org/ffmpeg.html#toc-Preset-files
         * @param string $preset_file_path 
         * @return Format Returns the current object.
         * @throws \InvalidArgumentException of the file does not exist.
         * @throws \InvalidArgumentException of the file is not readable.
         */
        public function setPresetOptionsFile($preset_file_path)
        {
            if($preset_file_path === null)
            {
                $this->_format['preset_options_file'] = null;
                return $this;
            }
            
            $preset_file_path = realpath($preset_file_path);
            
            if($preset_file_path === false || is_file($preset_file_path) === false)
            {
                throw new \InvalidArgumentException('Preset options file "'.$preset_file_path.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setPresetOptionsFile does not exist.');
            }
            else if(is_readable($preset_file_path) === false)
            {
                throw new \InvalidArgumentException('Preset preset options file "'.$preset_file_path.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setPresetOptionsFile is not readable.');
            }
            
            $this->_format['preset_options_file'] = $preset_file_path;
            return $this;
        }

        /**
         * Sets the output strictness (-strictness) determining what level of stable funcitonality is used.
         * By default "experimental" is used/
         *
         * @access public
         * @author Oliver Lillie
         * @param constant $strictness One of the following values. Format::STRICTNESS_VERY, Format::STRICTNESS_STRICT, 
         *  Format::STRICTNESS_NORMAL, Format::STRICTNESS_UNOFFICIAL, Format::STRICTNESS_EXPERIMENTAL
         * @return Format Returns the current object.
         * @throws \InvalidArgumentException If an unrecognised strictness value is returned.
         */
        public function setStrictness($strictness)
        {
            if($strictness === null)
            {
                $this->_format['strictness'] = null;
                return $this;
            }
            
            if(in_array($strictness, array(Format::STRICTNESS_VERY, Format::STRICTNESS_STRICT, Format::STRICTNESS_NORMAL, Format::STRICTNESS_UNOFFICIAL, Format::STRICTNESS_EXPERIMENTAL)) === true)
            {
                $this->_format['strictness'] = $strictness;
                return $this;
            }
            
            throw new \InvalidArgumentException('Unrecognised strictness "'.$strictness.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setStrictness');
        }
        
        /**
         * Sets the output format of the ffmpeg process, ie the -f <format> ffmpeg command
         *
         * @access public
         * @author Oliver Lillie
         * @param string $format One of the values returned from FfmpegParser::getFormats.
         * @return Format Returns the current object.
         * @throws \InvalidArgumentException If the format requested is "segment". This is a special ffmpeg format to split a file, however PHPVideoToolkit
         *  has a special function to segment files. 
         * @throws \InvalidArgumentException If an unregognised format is given.
         */
        public function setFormat($format)
        {
            // TODO work out what can be input and what can't be inputed
            
            if($format === null)
            {
                $this->_format['format'] = null;
                return $this;
            }
            
//          validate input
            $valid_formats = array_keys($this->getFormats());
            if(in_array($format, $valid_formats) === true)
            {
//              check to see if segmenting has been requested. If it has warn of the Media::split function instead.
                if($format === 'segment')
                {
                    throw new \InvalidArgumentException('You cannot set the format to segment, please use instead the function \\PHPVideoToolkit\\Media::segment.');
                }
                    
                $this->_format['format'] = $format;
                return $this;
            }
            
            throw new \InvalidArgumentException('Unrecognised format "'.$format.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setFormat');
        }
        
        /**
         * Sets the -threads <setting>
         *
         * @access public
         * @author Oliver Lillie
         * @param integer $threads Between 0-64
         * @param integer $stream_index Between 0-48. If specified then the threads option is given a stream specifier to 
         *  specify a particular audiovideo stream, i.e. -threads:1 4. If a previous setThreads has been called without specifiying
         *  a stream_index, then the 
         * @return Format Returns the current object.
         * @throws \InvalidArgumentException If the threads value is not an integer.
         * @throws \InvalidArgumentException If the threads value is not between 1-64.
         */
        public function setThreads($threads, $stream_index=null)
        {
            $this->_blockSetOnInputFormat('thread level');
            
            if($threads === null)
            {
                $this->_format['threads'] = null;
                return $this;
            }
            
            if(is_int($threads) === false)
            {
                throw new \InvalidArgumentException('The threads value must be an integer.');
            }
            else if($threads < 0 || $threads > 64)
            {
                throw new \InvalidArgumentException('Invalid `threads` value; the value must fit in range 0 - 64.');
            }

            // if we have a specified stream index then store the threads format differently.
            if($stream_index !== null)
            {
                if(is_int($stream_index) === false)
                {
                    throw new \InvalidArgumentException('The stream_index value must be an integer.');
                }
                else if($stream_index < 0 || $stream_index > 48)
                {
                    throw new \InvalidArgumentException('Invalid `stream_index` value; the value must fit in range 0 - 48.');
                }

                if($this->_format['threads_indexed'] === null)
                {
                    $this->_format['threads_indexed'] = array();
                }
                $this->_format['threads_indexed'][$stream_index] = $threads;
                return $this;
            }

            $this->_format['threads'] = $threads;
            return $this;
        }
        
        /**
         * Sets the -qscale <setting>
         *
         * @access public
         * @author Oliver Lillie
         * @see http://www.kilobitspersecond.com/2007/05/24/ffmpeg-quality-comparison/
         * @param integer $qscale Between 1-31
         * @return Format Returns the current object.
         * @throws \InvalidArgumentException If the qscale value is not an integer.
         * @throws \InvalidArgumentException If the qscale value is not between 1-64.
         */
        public function setQualityVsStreamabilityBalanceRatio($qscale)
        {
            $this->_blockSetOnInputFormat('quality stream ability balance ratio (qscale)');
            
            if($qscale === null)
            {
                $this->_format['quality'] = null;
                return $this;
            }
            
            if(is_int($qscale) === false)
            {
                throw new \InvalidArgumentException('The qscale value must be an integer.');
            }
            else if($qscale < 1 || $qscale > 31)
            {
                throw new \InvalidArgumentException('Invalid quality stream ability balance ratio (qscale) value; the value must fit in range 1 - 31.');
            }
            
            $this->_format['quality'] = $qscale;
            return $this;
        }
        
    }
