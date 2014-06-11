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
     * This class provides an abstract basis for all the gif transcoder engines.
     *
     * @author Oliver Lillie
     */
    abstract class AnimatedGifTranscoderAbstract
    {
        /**
         * A variable holder for the config object.
         * @var PHPVideoTookit\Config
         * @access protected
         */
        protected $_config;

        /**
         * A variable holder to contain the PHPVideoToolkit\Image frames.
         * @var array
         * @access protected
         */
        protected $_frames;

        /**
         * A variable holder that contains the loop count of the animated gif.
         * @var integer
         * @access protected
         */
        protected $_loop_count;
        
        /**
         * A variable holder that contains the frame delay between the frames of the animated gif.
         * @var mixed integer or float
         * @access protected
         */
        protected $_frame_delay;
        
        /**
         * Constructor
         *
         * @access public
         * @author: Oliver Lillie
         * @param  PHPVideoToolkit\Config $config The PHPVideoToolkit\Config object
         */
        public function __construct(Config $config=null)
        {
            $this->_config = $config === null ? Config::getInstance() : $config;
            $this->_frames = array();
            $this->_loop_count = AnimatedGif::UNLIMITED_LOOPS;
        }
        
        /**
         * Adds a frame to the current timeline.
         *
         * @access public
         * @author Oliver Lillie
         * @param PHPVideoToolkit\Image $image A PHPVideoToolkit\Image object to add to the animated gif frames array.
         * @return PHPVideoTookit\AnimatedGifTranscoderAbstract Returns the current object.
         */
        public function addFrame(Image $image)
        {
            array_push($this->_frames, $image->getMediaPath());
            
            return $this;
        }
        
        /**
         * Sets the animated gif loop count.
         *
         * @access public
         * @author Oliver Lillie
         * @param mixed $loop_count A positive integer or AnimatedGif::UNLIMITED_LOOPS to loop unlimitedly.
         * @return PHPVideoTookit\AnimatedGifTranscoderAbstract Returns the current object.
         * @throws \InvalidArgumentException If the $loop_count value is less than -1
         * @throws \InvalidArgumentException If the $loop_count value is not an integer.
         */
        public function setLoopCount($loop_count)
        {
            if(is_integer($loop_count) === false)
            {
                throw new \InvalidArgumentException('The loop count must be an integer value.');
            }
            if($loop_count < 0 && $loop_count !== AnimatedGif::UNLIMITED_LOOPS)
            {
                throw new \InvalidArgumentException('The loop count cannot be less than 0. (AnimatedGif::UNLIMITED_LOOPS specifies unlimited looping)');
            }
            $this->_loop_count = $loop_count;
            
            return $this;
        }
        
        /**
         * Sets the frame delay between frames.
         *
         * @access public
         * @author Oliver Lillie
         * @param mixed $loop_count A positive integer or AnimatedGif::UNLIMITED_LOOPS
         * @return PHPVideoTookit\AnimatedGifTranscoderAbstract Returns the current object.
         * @throws \InvalidArgumentException If the $loop_count value is less than -1
         * @throws \InvalidArgumentException If the $loop_count value is not an integer.
         */
        public function setFrameDelay($frame_delay)
        {
            if(is_integer($frame_delay) === false && is_float($frame_delay) === false)
            {
                throw new \InvalidArgumentException('The frame delay must be an integer value.');
            }
            if($frame_delay < 0.001)
            {
                throw new \InvalidArgumentException('The frame delay cannot be less than 0.001.');
            }
            $this->_frame_delay = $frame_delay;
            
            return $this;
        }
        
       /**
         * Saves the animated gif.
         *
         * @access public
         * @author: Oliver Lillie
         * @param  string $save_path The path to save the animated gif to.
         * @param  constant $overwrite Determines the file overwrite status. Can be one of the following values.
         *  PHPVideoToolkit::Media::OVERWRITE_FAIL
         *  PHPVideoToolkit::Media::OVERWRITE_EXISTING
         *  PHPVideoToolkit::Media::OVERWRITE_UNIQUE
         * @return string Returns the save path of the animated gif.
         * @throws \RuntimeException If $overwrite is set to PHPVideoToolkit::Media::OVERWRITE_FAIL and the $save_path already exists.
         * @throws \RuntimeException If $overwrite is set to PHPVideoToolkit::Media::OVERWRITE_EXISTING and the $save_path is not writable.
         */
        public function save($save_path, $overwrite=Media::OVERWRITE_FAIL)
        {
            if(empty($this->_frames) === true)
            {
                throw new \InvalidArgumentException('At least one frame must be added in order to save an animated gif.');
            }
            
            if(is_file($save_path) === true)
            {
                if(empty($overwrite) === true || $overwrite === Media::OVERWRITE_FAIL)
                {
                    throw new \RuntimeException('The output file already exists and overwriting is disabled.');
                }
                else if($overwrite === Media::OVERWRITE_EXISTING && is_writeable(dirname($save_path)) === false)
                {
                    throw new \RuntimeException('The output file already exists, overwriting is enabled however the file is not writable.');
                }

                switch($overwrite)
                {
                    case Media::OVERWRITE_EXISTING :
                        @unlink($save_path);
                        break;
                        
//                  insert a unique id into the save path
                    case Media::OVERWRITE_UNIQUE :
                        $pathinfo = pathinfo($save_path);
                        $save_path = $pathinfo['dirname'].DIRECTORY_SEPARATOR.$pathinfo['filename'].'-u_'.String::generateRandomString().'.'.$pathinfo['extension'];
                        break;
                }
            }
            return $save_path;
        }
    }
