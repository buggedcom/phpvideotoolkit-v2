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
     * Automatically sets the required codecs and formats for the mp3 audio format.
     *
     * @author Oliver Lillie
     */
    class AudioFormat_Mp3 extends AudioFormat
    {
        /**
         * Constructor
         *
         * @access public
         * @author Oliver Lillie
         * @param  constant $input_output_type Determines the input/output type of the Format. Either PHPVideoToolkit\Format::INPUT 
         *  or PHPVideoToolkit\Format::OUTPUT
         * @param  PHPVideoToolkit\Config $config The config object.
         */
        public function __construct($input_output_type=Format::OUTPUT, Config $config=null)
        {
            parent::__construct($input_output_type, $config);
            
            if($input_output_type === 'output')
            {
                $this->setAudioCodec('libmp3lame')
                     ->setFormat('mp3');
            }
            
            $this->_restricted_audio_codecs = array('libmp3lame', 'mp3');
        }
    }
