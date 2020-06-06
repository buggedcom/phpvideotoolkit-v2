<?php
    
    /**
     * This file is part of the PHP Video Toolkit v2 package.
     *
     * @author Ivan Sinkarenko (aka ivany4)
     * @license MIT
     * @package PHPVideoToolkit V2
     * @version 2.2.6
     * @uses ffmpeg http://ffmpeg.sourceforge.net/
     */
     
    namespace PHPVideoToolkit;

    /**
     * @access public
     * @author ivany4
     * @package default
     */
    class VideoFormat_Av1 extends VideoFormat
    {
        public function __construct($input_output_type=Format::OUTPUT, Config $config=null)
        {
            parent::__construct($input_output_type, $config);
            
            if($input_output_type === 'output')
            {
                $this->setAudioCodec('opus')
                     ->setVideoCodec('av1')
                     ->setFormat('webm');
            }
            
            $this->_restricted_audio_codecs = array('libopus', 'opus');
            $this->_restricted_video_codecs = array('libaom-av1', 'av1');
        }
    }
