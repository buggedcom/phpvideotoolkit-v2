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
     * This class provides an animated gif transcoder engine that uses imagemagick convert server programme to create the
     * animated gif.
     *
     * @author Oliver Lillie
     */
    class AnimatedGifTranscoderConvert extends AnimatedGifTranscoderAbstract
    {
        /**
         * Saves the animated gif.
         *
         * @access public
         * @author Oliver Lillie
         * @param  string $save_path The path to save the animated gif to.
         * @return Image Returns a new instance of PHPVideoToolkit\Image with the new animated gif as the src.
         * @throws AnimatedGifException If the convert process encounters an error.
         */
        public function save($save_path)
        {
            $save_path = parent::save($save_path);
            
//          build the gifsicle process
            $process = new ProcessBuilder('convert', $this->_config);
            
//          set the frame duration
            $process->add('-delay')->add($this->_frame_delay*100);
            $process->add('-loop')->add($this->_loop_count === AnimatedGif::UNLIMITED_LOOPS ? '0' : $this->_loop_count+1);

//          add in all the frames
            foreach ($this->_frames as $path)
            {
                $process->add($path);
            }

            if($this->_config->gif_transcoder_convert_use_dither === true)
            {
                $process->add('-ordered-dither')->add($this->_config->gif_transcoder_convert_dither_order);
            }
            if($this->_config->gif_transcoder_convert_use_coalesce === true)
            {
                $process->add('-coalesce');
            }
            $process->add('-layers')->add('OptimizeTransparency');
            if($this->_config->gif_transcoder_convert_use_map === true)
            {
                $process->add('+map');
            }

//          add the output path
            $process->add($save_path);
            
//          execute the process.
            $exec = $process->getExecBuffer();
            $exec->setBlocking(true)
                 ->execute();
            
//          check for any gifsicle errors
            if($exec->hasError() === true)
            {
                throw new AnimatedGifException('AnimatedGif save using `convert` "'.$save_path.'" failed. Any additional convert message follows: 
'.$exec->getBuffer());
            }
            
            return new Image($save_path, $this->_config);
        }
        
        /**
         * Determines if the convert transcoder engine is available on the current system.
         *
         * @access public
         * @static
         * @author Oliver Lillie
         * @param  Config $config The configuration object.
         * @return boolean Returns true if this engine can be used, otherwise false.
         */
        public static function available(Config $config)
        {
            if($config->convert === null)
            {
                return false;
            }
            
            try
            {
                Binary::locate($config->convert);
                return true;
            }
            catch(BinaryException $e)
            {
                return false;
            }
        }
    }
