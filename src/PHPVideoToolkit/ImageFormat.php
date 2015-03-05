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
    class ImageFormat extends VideoFormat
    {
        protected $_max_frames_on_no_timecode = true;

        public function updateFormatOptions(&$save_path, $overwrite)
        {
            parent::updateFormatOptions($save_path, $overwrite);
            
            if($this->_type === Format::OUTPUT)
            {
                if($this->_max_frames_on_no_timecode === true && preg_match('/%timecode|%index|%[0-9]*d/', $save_path) === 0)
                {
                    $this->setVideoMaxFrames(1);
                }
            }
            
            return $this;
        }
    }
