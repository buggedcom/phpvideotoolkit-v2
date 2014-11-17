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
     * This class extends PHPVideoToolkit\Media. If provides some additional required commands if the input
     * is an audio file.
     *
     * @author Oliver Lillie
     */
    class Audio extends Media
    {
        /**
         * Constructor
         *
         * @access public
         * @author Oliver Lillie
         * @param  string $audio_file_path The path to the audio file.
         * @param  PHPVideoToolkit\Config $config The config object.
         * @param  PHPVideoToolkit\AudioFormant $audio_input_format The input format object to use, if any. Otherwise null
         * @param  boolean $ensure_audio_file If true an additional check is made to ensure the the given file is actually an audio file.
         * @throws \LogicException If $ensure_audio_file is true but the file is not audio.
         */
        public function __construct($audio_file_path, Config $config=null, AudioFormat $audio_input_format=null, $ensure_audio_file=true)
        {
            parent::__construct($audio_file_path, $config, $audio_input_format);
            
//          validate this media file is an audio file
            if($ensure_audio_file === true && $this->_validateMedia('audio') === false)
            {
                throw new \LogicException('You cannot use an instance of '.get_class($this).' for "'.$audio_file_path.'" as the file is not an audio file. It is reported to be a '.$this->readType());
            }
        }
        
        /**
         * Determines the default format class name if none is set when calling Formats::getFormatFor.
         *
         * @access public
         * @author Oliver Lillie
         * @return string
         */
        public function getDefaultFormatClassName()
        {
            return 'AudioFormat';
        }
        
        /**
         * Adds some commands to the FFmpeg command string if the media file is being split into parts.
         *
         * @access public
         * @author Oliver Lillie
         * @param  PHPVideoToolkit\Format $output_format The output format being used to save the output media.
         * @param  string $save_path The save path of the output media.
         * @param  constant $overwrite The Media constant used to determine the overwrite status of the save. One of the 
         *  following constants:
         *  PHPVideoToolkit\Media::OVERWRITE_FAIL
         *  PHPVideoToolkit\Media::OVERWRITE_EXISTING
         *  PHPVideoToolkit\Media::OVERWRITE_UNIQUE
         * @param  PHPVideoToolkit\ProgressHandlerAbstract $progress_handler The progress handler attached to the save, if any. 
         * @return void
         */
        protected function _savePreProcess(Format &$output_format=null, &$save_path, $overwrite, ProgressHandlerAbstract &$progress_handler=null)
        {
            parent::_savePreProcess($output_format, $save_path, $overwrite, $progress_handler);
            
//          if we are splitting the output
            if(empty($this->_split_options) === false)
            {
                $options = $output_format->getFormatOptions();
            
//              if we are splitting we need to add certain commands to make it work.
//              for video, we need to ensure that just the audio codec is set.
                if(empty($options['audio_codec']) === true)
                {
                    $this->_process->addCommand('-acodec', 'copy');
                }
            }
        }
        
        /**
         * Runs a check to see if the audio has been disabled but no other output found. If so an exception is thrown.
         *
         * @access public
         * @author Oliver Lillie
         * @param  PHPVideoToolkit\Format $output_format The output format being used to save the output media.
         * @param string &$save_path The save path of the output file
         * @param  constant $overwrite The Media constant used to determine the overwrite status of the save. One of the 
         *  following constants:
         *  PHPVideoToolkit\Media::OVERWRITE_FAIL
         *  PHPVideoToolkit\Media::OVERWRITE_EXISTING
         *  PHPVideoToolkit\Media::OVERWRITE_UNIQUE
         * @return void
         * @throws \LogicException If audio is disabled and no layers, prepends or appends are found.
         */
        protected function _processOutputFormat(Format &$output_format=null, &$save_path, $overwrite)
        {
            parent::_processOutputFormat($output_format, $save_path, $overwrite);
            
//          check for conflictions with having audio disabled.
            $options = $output_format->getFormatOptions();
            if($options['disable_audio'] === true && empty($this->_layers) === true && empty($this->_prepends) === true && empty($this->_appends) === true)
            {
                throw new \LogicException('Unable to process output format to send to ffmpeg as audio has been disabled and no other inputs have been found.');
            }
        }
    }
