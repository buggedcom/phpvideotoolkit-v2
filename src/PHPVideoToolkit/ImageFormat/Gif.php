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
    class ImageFormat_Gif extends ImageFormat
    {
        protected $_max_frames_on_no_timecode = false;
       
        public function __construct($input_output_type=Format::OUTPUT, Config $config=null)
        {
            parent::__construct($input_output_type, $config);
            
            $this->_format = array_merge($this->_format, array(
                'gif_loop_count' => AnimatedGif::UNLIMITED_LOOPS,
                'gif_frame_delay' => 0.1,
            ));
            
            $this->_format_to_command = array_merge($this->_format_to_command, array(
                'gif_loop_count'            => '',
                'gif_frame_delay'           => '',
            ));
            
            if($input_output_type === 'output')
            {
                $this->disableAudio()
                     ->setVideoCodec('gif')
                     ->setFormat('gif');
            }
            
            $this->_restricted_audio_codecs = array();
            $this->_restricted_video_codecs = array('gif');
        }
        
        public function setLoopCount($loop_count)
        {
            $this->_blockSetOnInputFormat('animated gif loop count');
            
            if($loop_count === null)
            {
                $this->_format['gif_loop_count'] = null;
                return $this;
            }
            
            if($loop_count !== null && $loop_count < -1)
            {
                throw new \InvalidArgumentException('The loop count cannot be less than -1. (-1 specifies unlimited looping)');
            }
            
            $this->_format['gif_loop_count'] = (int) $loop_count;
            
            return $this;
        }
        
        public function setFrameDelay($frame_delay)
        {
            $this->_blockSetOnInputFormat('animated gif frame delay');
            
            if($frame_delay === null)
            {
                $this->_format['gif_frame_delay'] = null;
                return $this;
            }
            
            if($frame_delay !== null && $frame_delay <= 0)
            {
                throw new \InvalidArgumentException('The animated gif frame delay cannot be equal to or less than 0.');
            }
            if(is_int($frame_delay) === false && is_float($frame_delay) === false)
            {
                throw new \InvalidArgumentException('The animated gif frame delay value must either be an integer or a float.');
            }
            
            $this->_format['gif_frame_delay'] = $frame_delay;
            
            return $this;
        }
        
        public function updateFormatOptions(&$save_path, $overwrite)
        {
            parent::updateFormatOptions($save_path, $overwrite);
            
//          if the save path doesn't have %d in it then we are ouputing an animated gif,
//          otherwise it is assumed that the output is multiple images.
//          If we are going to output an animated gif we must prevent ffmpeg from doing it.
//          This is because ffmpeg creates really shitty animated gifs, which is suprising.
            if(preg_match('/(\%([0-9]*)?index|timecode)/', $save_path, $matches) === 0)
            {
//              if the frame rate has not been set, find out what it is and then set it
                if(empty($this->_format['video_frame_rate']) === true)
                {
                    $frame_rate = $this->_media_object->getFrameRate();
                    $this->setVideoFrameRate(floor($frame_rate));
                }
                
//              as we are outputting frames we want the png format for each frame for best possible output
                $this->_restricted_video_codecs = array('png');
                $this->setVideoCodec('png')
                     ->setFormat('image2');
                $this->_restricted_video_codecs = array('gif');
                
//              update the pathway to include indexed output so that it outputs multiple frames.
                $original_save_path = $save_path;
                $ext = pathinfo($save_path, PATHINFO_EXTENSION);
                $filename = 'phpvideotoolkit_anigif_'.Str::generateRandomAlphaString(5).'_'.basename(substr_replace($save_path, '%12index.png', -(strlen($ext)+1)));
                $save_path = $this->_config->temp_directory.DIRECTORY_SEPARATOR.$filename;
                
//              register the post process to combine the images into an animated gif
                $this->_media_object->registerOutputPostProcess(array($this, 'postProcessCreateAnimatedGif'), array($original_save_path, $overwrite, $this->_format['video_frame_rate'], $this->_format['gif_loop_count'], $this->_format['gif_frame_delay']));
            }
            
            return $this;
        }
        
        public function postProcessCreateAnimatedGif(array $output, Media $media, $save_path, $overwrite, $video_frame_rate, $gif_loop_count, $gif_frame_delay)
        {
//          create the gif
            $gif = AnimatedGif::createFrom($output, 1/$video_frame_rate, $gif_loop_count, $this->_config);
            
//          break out the dirname incase of relative pathways.
            $name = basename($save_path);
            $path = realpath(dirname($save_path));
            $save_path = $path.DIRECTORY_SEPARATOR.$name;
            
//          save the gif
            $image = $gif->setFrameDelay($gif_frame_delay)
                         ->setOverwriteMode($overwrite)
                         ->save($save_path, $overwrite);
            
//          remove tmp frame files
            foreach ($output as $output_image)
            {
                @unlink($output_image->getMediaPath());
            }

//          return an updated output
            return $image;
        }
        
        
    }
