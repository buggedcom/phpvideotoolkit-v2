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
    class VideoFormat_Mp4 extends VideoFormat_H264
    {
        protected $_post_process_qt_faststart;
        protected $_enforce_qt_faststart_success;

        public function __construct($input_output_type=Format::OUTPUT, Config $config=null)
        {
            parent::__construct($input_output_type, $config);
            
            if($input_output_type === 'output')
            {
                $this->setFormat('mp4')
                     ->setAudioCodec('aac');
            }
            
            $this->forceQtFastStartSuccess();

//          determine if we are using qtfaststart from the config object.
//          remember doing so puts any save into blocking mode so if you are using ProgressHandlerPortable
//          you must access the $process->getPortableId() before calling save or saveNonBlocking.
            if($this->_config->force_enable_qtfaststart === true)
            {
                $this->enableQtFastStart();
            }
        }
        
        public function enableQtFastStart()
        {
            $this->_post_process_qt_faststart = true;
        }
        
        public function disableQtFastStart()
        {
            $this->_post_process_qt_faststart = false;
        }
        
        public function allowQtFastStartFailure()
        {
            $this->_enforce_qt_faststart_success = false;
        }
        
        public function forceQtFastStartSuccess()
        {
            $this->_enforce_qt_faststart_success = true;
        }

        public function updateFormatOptions(&$save_path, $overwrite)
        {
            parent::updateFormatOptions($save_path, $overwrite);
            
//          assign a post process so that qt-faststart (https://ffmpeg.org/trac/ffmpeg/wiki/UbuntuCompilationGuide#qt-faststart) changes the qt atom to allow fast streaming.
            if($this->_post_process_qt_faststart === true)
            {
                $this->_media_object->registerOutputPostProcess(array($this, 'postProcessFastStart'));
            }
            
            return $this;
        }
        
        /**
         * Specifically for creating fast starting files.
         * however it can also be used as a standalone function call from the H264Format object.
         *
         * @access public
         * @author Oliver Lillie
         * @param Media $media 
         * @return Media
         */
        public function postProcessFastStart(Media $media)
        {
            // TODO possibly look at setting -movflags faststart options on ffmpeg instead of this.
            
//          set the yamdi input and output options.
            $output = $media->getMediaPath();
            $temp_output = $output.'.qtfaststart.'.pathinfo($output, PATHINFO_EXTENSION);

//          build the qtfaststart process
            $qtfaststart_process = new ProcessBuilder('qtfaststart', $this->_config);
            $exec = $qtfaststart_process
                          ->add($output)
                          ->add($temp_output)
                          ->getExecBuffer();
                
//          execute the process.
            $exec->setBlocking(true)
                 ->execute();
                
//          check for any qt-faststart errors
            if($exec->hasError() === true)
            {
                if(is_file($temp_output) === true)
                {
                    //@unlink($temp_output);
                }
                if($this->_enforce_qt_faststart_success === true)
                {
                    //@unlink($output);
                    throw new FfmpegProcessPostProcessException('qt-faststart post processing of "'.$output.'" failed. The output file has been removed. Any additional qt-faststart message follows: 
'.$exec->getExecutedCommand().'
'.$exec->getBuffer());
                }
                // TODO, log or exception not sure as the original file is ok.
            }
            else
            {
//              nope everything went ok. so delete ffmpeg file, and then rename yamdi file to that of the original.
                unlink($output);
                rename($temp_output, $output);
            }
            
            return $media;
        }
    }
