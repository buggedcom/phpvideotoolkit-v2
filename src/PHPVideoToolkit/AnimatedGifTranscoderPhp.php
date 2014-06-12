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
     
    use GifCreator; 
     
    /**
     * This class provides an animated gif transcoder engine that uses pure PHP to create an animated gif. This is the
     * weakest format of the three engines and should be avoided if possible.
     *
     * @author Oliver Lillie
     */
    class AnimatedGifTranscoderPhp extends AnimatedGifTranscoderAbstract
    {
        /**
         * Saves the animated gif.
         *
         * @access public
         * @author Oliver Lillie
         * @param  string $save_path The path to save the animated gif to.
         * @return PHPVideoToolkit\Image Returns a new instance of PHPVideoToolkit\Image with the new animated gif as the src.
         * @throws PHPVideoToolkit\AnimatedGifException If an empty gif is generated.
         * @throws PHPVideoToolkit\AnimatedGifException If the gif couldn't be saved to the filesystem.
         */
        public function save($save_path)
        {
            $save_path = parent::save($save_path);

//          build the gif creator process
            require_once dirname(dirname(dirname(__FILE__))).'/vendor/sybio/gif-creator/src/GifCreator/GifCreator.php';
            $gc = new \GifCreator\GifCreator();
            
//          add in all the frames
            $durations = array();
            $frame_duration = $this->_frame_delay*100;
            foreach ($this->_frames as $path)
            {
                array_push($durations, $frame_duration);
            }
            $gc->create($this->_frames, $durations, $this->_loop_count === AnimatedGif::UNLIMITED_LOOPS ? '0' : $this->_loop_count+1);
            $gif_data = $gc->getGif();
            
//          check for errors or put the data into the file.
            if(empty($gif_data) === true)
            {
                throw new AnimatedGifException('AnimatedGif using `php` generated an empty gif.');
            }
            if(file_put_contents($save_path, $gif_data) === false)
            {
                throw new AnimatedGifException('AnimatedGif save to filesystem failed using "'.$save_path.'".');
            }

            return new Image($save_path, $this->_config);
        }
        
        /**
         * Determines if the php transcoder engine is available on the current system.
         *
         * @access public
         * @static
         * @author Oliver Lillie
         * @param  PHPVideoToolkit\Config $config The configuration object.
         * @return boolean Returns true if this engine can be used, otherwise false.
         */
        public static function available(Config $config)
        {
            return function_exists('imagegif') && is_file(dirname(dirname(dirname(__FILE__))).'/vendor/sybio/gif-creator/src/GifCreator/GifCreator.php');
        }
    }
