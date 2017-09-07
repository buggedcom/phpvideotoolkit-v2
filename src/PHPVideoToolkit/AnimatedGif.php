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
     * This class provides a wrapper class to create animated gif from differing transcoders engines.
     * 
     * @author Oliver Lillie
     */
    class AnimatedGif
    {
        /**
         * The constant to determine that unlimited loops are required when calling setLoopCount.
         * @var string
         **/
        const UNLIMITED_LOOPS = -1;
        
        /**
         * A variable holder for the animated gif transcoder object.
         * @var PHPVideoTookit\AnimatedGifTranscoderAbstract
         * @access protected
         */
        protected $_transcoder;

        /**
         * A variable holder for the path to the animated gif.
         * @var string
         */
        protected $_file_path;

        /**
         * A variable holder for the config object.
         * @var PHPVideoTookit\Config
         * @access protected
         */
        protected $_config;
        
        /**
         * Constructor
         *
         * @access public
         * @author Oliver Lillie
         * @param  mixed $gif_path If a gif is given then the string path, otherwise if a gif is to be generated, null.
         * @param  Config $config The PHPVideoToolkit\Config object
         * @throws \RuntimeException If the gif path is set but doesn't exist.
         * @throws \RuntimeException If the gif is not readable.
         * @throws \InvalidArgumentException If the image supplied is not an image.
         * @throws \InvalidArgumentException If the image supplied is not a gif image.
         * @throws \InvalidArgumentException If the gif transcoder engine supplied to the Config object is not recognised.
         * @throws \LogicException If the chosen gif transcoder engine is not available on the current system.
         * @throws \LogicException If none of the transcoder engines are available on your system.
         */
        public function __construct($gif_path=null, Config $config=null)
        {
            $this->_config = $config === null ? Config::getInstance() : $config;
            
//          if we have a file, check that it exists, is readable, is an image and is a gif.
            if($gif_path !== null)
            {
                $real_file_path = realpath($gif_path);
                if($real_file_path === false || is_file($real_file_path) === false)
                {
                    throw new \RuntimeException('The file does not exist.');
                }
                else if(is_readable($real_file_path) === false)
                {
                    throw new \RuntimeException('The file is not readable.');
                }
                else if(($image_info = getimagesize($real_file_path)) === false)
                {
                    throw new \InvalidArgumentException('The file is not an image.');                   
                }
                else if($image_info[2] !== IMAGETYPE_GIF)
                {
                    throw new \InvalidArgumentException('The file is not a gif.');                  
                }
                $gif_path = $real_file_path;
            }
            $this->_file_path = $gif_path;
            
//          validate the transcoder engine if set
            if(in_array($this->_config->gif_transcoder, array('gifsicle', 'convert', 'php', null)) === false)
            {
                throw new \InvalidArgumentException('Unrecognised transcoder engine.');
            }
            
//          auto detect a transcoder based on order of preference.
            $transcoder_engine = null;
            if($this->_config->gif_transcoder === null)
            {
                $transcoder_preference = array('gifsicle', 'convert', 'php');
                foreach ($transcoder_preference as $transcoder)
                {
                    $transcoder_class = '\\PHPVideoToolkit\\AnimatedGifTranscoder'.ucfirst($transcoder);
                    if(call_user_func(array($transcoder_class, 'available'), $this->_config) === true)
                    {
                        $transcoder_engine = $transcoder_class;
                        break;
                    }
                }
            }
            else
            {
//              create the transcoder and check it's available
                $transcoder_class = '\\PHPVideoToolkit\\AnimatedGifTranscoder'.ucfirst($this->_config->gif_transcoder);
                if(call_user_func(array($transcoder_class, 'available'), $this->_config) === false)
                {
                    throw new \LogicException('The transcoder engine "'.$this->_config->gif_transcoder.'" is not available on your system.');
                }
                $transcoder_engine = $transcoder_class;
            }
            if($transcoder_engine === null)
            {
                throw new \LogicException('There are no available transcoders on your system.');
            }
            
            $this->_transcoder = new $transcoder_engine($this->_config);
        }
        
        /**
         * Returns the current gif file path, if set.   
         *
         * @access public
         * @author Oliver Lillie
         * @return string The path of the current gif if set.
         */
        public function getFilePath()
        {
            return $this->_file_path;
        }
        
        /**
         * Calls any method in the contained $_transcoder class.
         *
         * @access public
         * @author Oliver Lillie
         * @param string $name 
         * @param array $arguments 
         * @return mixed
         * @throws \BadMethodCallException if the function called does not exist or is not callable.
         */
        public function __call($name, $arguments)
        {
            if(method_exists($this->_transcoder, $name) === true && is_callable(array($this->_transcoder, $name)) === true)
            {
                return call_user_func_array(array($this->_transcoder, $name), $arguments);
            }
            else
            {
                throw new \BadMethodCallException('`'.$name.'` is not a valid animated gif transcoder function.');
            }
        }

        /**
         * Creates a new animated gif object from a selection of files.
         *
         * @access public
         * @static
         * @author Oliver Lillie
         * @param  array $image_object_array An array of PHPVideoToolkit\Image objects to use to create the animated gif.
         * @param  mixed $frame_delay An integer or float value to determine the delay between gif frames.  
         * @param  integer $loop_count The number of times the animated gif should loop. Specify -1 for an endless loop.
         * @param  Config $config The PHPVideoToolkit\Config object.
         * @return AnimatedGif Returns a PHPVideoToolkit\AnimatedGif object.
         * @throws \InvalidArgumentException If the $image_object_array is empty.
         * @throws \InvalidArgumentException If the $frame_delay is less than 0 or not an integer or float.
         * @throws \InvalidArgumentException If any of the values within $image_object_array is not an instance of PHPVideoToolkit\Image.
         */
        public static function createFrom(array $image_object_array, $frame_delay, $loop_count=self::UNLIMITED_LOOPS, Config $config=null)
        {
            if(empty($image_object_array) === true)
            {
                throw new \InvalidArgumentException('At least one file path must be specified when creating an animated gif from AnimatedGif::createFrom.');
            }
            if($frame_delay <= 0)
            {
                throw new \InvalidArgumentException('The frame delay must be greater than 0.');
            }
            else if(is_int($frame_delay) === false && is_float($frame_delay) === false)
            {
                throw new \InvalidArgumentException('The frame delay must be either an integer or float value.');
            }

//          create a new gif and add all the frames.
            $gif = new self(null, $config);
            foreach ($image_object_array as $key=>$image)
            {
                if(is_object($image) === false || get_class($image) !== 'PHPVideoToolkit\\Image')
                {
                    throw new \InvalidArgumentException('The image at key '.$key.' is not an \\PHPVideoToolkit\\Image object. Each frame must be an Image object.');
                }
                
                $gif->addFrame($image, $frame_delay);
            }
            
//          set the loop count
            $gif->setLoopCount($loop_count);
            
            return $gif;
        }
        
        /**
         * Expands an animated gif into a list of files.
         *
         * @todo
         * @access public
         * @static
         * @author Oliver Lillie
         */
        public static function expand()
        {
            trigger_error('PHPVideoToolkit\Animatedgif::expand() has not yet been implemented.', E_USER_NOTICE);
        }
    }
