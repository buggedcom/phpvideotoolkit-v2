<?php

    /**
     * This is a pure php emulation of the PHP module FFmpeg-PHP.
     * There is one extra function here. ffmpeg_animated_gif::saveNow();
     * @author Oliver Lillie (aka buggedcom) <publicmail@buggedcom.co.uk>
     * @package PHPVideoToolkit
     * @license BSD
     * @copyright Copyright (c) 2008 Oliver Lillie <http://www.buggedcom.co.uk>
     * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation
     * files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy,
     * modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software
     * is furnished to do so, subject to the following conditions:  The above copyright notice and this permission notice shall be
     * included in all copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
     * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
     * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
     * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
     * @uses GifEncoder 
     *      - @link http://www.phpclasses.org/browse/package/3163.html
     *      - @link http://phpclasses.gifs.hu/show.php?src=GIFEncoder.class.php
     *      - @author László Zsidi
     *      - @license Freeware.
     * @see ffmpeg-php, 
     *      - @link http://ffmpeg-php.sourceforge.net/
     *      - @author Todd Kirby.
     *      - all phpdoc documentation is lifted directly from the ffmpeg-php docs
     */
    
    if(!defined('DS'))
    {
        define('DS', DIRECTORY_SEPARATOR);
    }
    
    class ffmpeg_animated_gif
    {
        
        private $_frames            = array();
        private $_width             = null;
        private $_height            = null;
        private $_frame_rate        = null;
        private $_loop_count        = null;
        private $_output_file_path  = null;
        private $_toolkit_class     = null;
        private $_unlink_files      = array();
        private $_saved             = false;
        
        /**
         * Class Constructor
         * Create a new ffmpeg_animated_gif object.
         * @param resource $output_file_path Location in the filesystem where the animated gif will be written.
         * @param resource $width Width of the animated gif.
         * @param resource $height Height of the animated gif.
         * @param resource $frame_rate Frame rate of the animated gif in frames per second.
         * @param resource $loop_count Number of times to loop the animation. Put a zero here to loop forever or omit this parameter to disable looping.
         */
        function __construct($output_file_path, $width, $height, $frame_rate, $loop_count=false)
        {
            $this->_output_file_path    = $output_file_path;
            $this->_width               = $width;
            $this->_height              = $height;
            $this->_frame_rate          = $frame_rate;
            $this->_loop_count          = $loop_count;
        }
        
        /**
         * Class Destructor
         * saves the file
         */
        function __destruct()
        {
//          save the output
            $this->saveNow();
//          destroy all image resources
            if(count($this->_frames))
            {
                foreach($this->_frames as $key=>$frame)
                {
                    imagedestroy($frame->toGDImage());
                }
            }
//          loop through the temp files to remove 
            if(!empty($this->_unlink_files))
            {
                foreach ($this->_unlink_files as $key=>$file)
                {
                    @unlink($file);
                }
                $this->_unlink_files = array();
            }
        }
        
        /**
         * This function IS NOT PROVIDED IN FFMPEG-PHP as it creates the gif as frames are added. to save memory in
         * php and practicality purposes this isn't really possible. It will overwrite any file.
         * @access public
         * @uses GifEncoder
         *      - @link http://www.phpclasses.org/browse/package/3163.html
         *      - @link http://phpclasses.gifs.hu/show.php?src=GIFEncoder.class.php
         *      - @author László Zsidi
         *      - @license Freeware.
         * @param string $tmp_directory The temp directory to work with. (remember the trailing slash)
         * @return boolean
         */
        public function saveNow($tmp_directory='/tmp/')
        {
            if($this->_saved === false)
            {
                $this->_saved = true;
//              check there are frames to make a gif
                if(!count($this->_frames))
                {
                    return false;
                }
                if(!class_exists('GIFEncoder'))
                {
                    require_once dirname(__FILE__).DS.'gifencoder'.DS.'GIFEncoder.class.phpvideotoolkit.php';
                }
//              save all the images from the ffmpeg_frames
                $files = array();
                $delays = array();
                $delay = (1/$this->_frame_rate)*100;
                foreach($this->_frames as $key=>$frame)
                {
                    $file = $tmp_directory.'fag-'.uniqid(time().'-').'.gif';
                    if(!imagegif($frame->toGDImage(), $file))
                    {
                        return false;
                    }
//                  add file to array so it out deletes on close
                    array_push($this->_unlink_files, $file);
                    array_push($files, $file);
                    array_push($delays, $delay);
                }
//              convert the images
                $gif = new GIFEncoder($files, $delays, $this->_loop_count, 2, 0, 0, 0, 0, 'url');
                $gif_data = $gif->GetAnimation();
                if(!$gif_data)
                {
                    return false;
                }
//              write the gif
                if (!$handle = fopen($this->_output_file_path, 'w'))
                {
                    return false;
                }
                if (fwrite($handle, $gif_data) === false)
                {
                    return false;
                }
                fclose($handle);
                return true;
            }
            return false;
        }
//      NOTE this provides a way to do it through pure PHPVideoToolkit class, however when ffmpeg creates animated gifs it uses a 
//      limited colour palette for some stupid reason so the gifs created look rubbish.
//      public function saveNow($tmp_directory='/tmp/')
//      {
//          if($this->_saved === false)
//          {
//              $this->_saved = true;
// //               check there are frames to make a gif
//              if(!count($this->_frames))
//              {
//                  return false;
//              }
//              if($this->_toolkit !== null)
//              {
//                  $this->_toolkit->reset();
//              }
//              else
//              {
// //                   get the ffmpeg class
//                  require_once dirname(dirname(dirname(__FILE__))).DS.'phpvideotoolkit.php5.php';
//                  $this->_toolkit = new PHPVideoToolkit($tmp_directory);
//              }
// //               save all the images from the ffmpeg_frames
//              $files = array();
//              foreach($this->_frames as $key=>$frame)
//              {
//                  $file = $tmp_directory.'fag-'.$this->_toolkit->unique().'.jpg';
//                  if(!imagejpeg($frame->toGDImage(), $file, 80))
//                  {
//                      return false;
//                  }
// //                   add file to array so it out deletes on close
//                  array_push($this->_unlink_files, $file);
//                  array_push($files, $file);
//              }
// //               print_r($files);
// //               prepare these images for conversion into a movie/gif
//              $result = $this->_toolkit->prepareImagesForConversionToVideo($files);
//              if(!$result)
//              {
//                  return false;
//              }
// //               set the width and height
//              $this->_toolkit->setVideoOutputDimensions($this->_width, $this->_height);
// //               set the frame rate
//              $this->_toolkit->addCommand('-inputr', $this->_frame_rate);
// //               set the looping
//              $this->_toolkit->setGifLoops($this->_loop_count);
// //               set the output parameters
//              $output_path_info = pathinfo($this->_output_file_path);
//              $result = $this->_toolkit->setOutput($output_path_info['dirname'].'/', $output_path_info['basename'], PHPVideoToolkit::OVERWRITE_EXISTING);
//              if(!$result)
//              {
//                  return false;
//              }
// //               execute the ffmpeg command
//              $result = $this->_toolkit->execute();
//              print_r($this->_toolkit->getLastCommand());
//              if(!$result)
//              {
//                  return false;
//              }
//              return true;
//          }
//          return false;
//      }
        
        /**
         * Add a frame to the end of the animated gif.
         * @access public
         * @param resource $output_file_path The ffmpeg_frame object to add to the end of the animated gif.
         * @param boolean $save If true the gif will save after the frame has been added. 
         *      NOTE: this param does not feature in the actuall ffmpeg-php module.
         * @return boolean
         */
        public function addFrame($frame_to_add, $save=false)
        {
            if(get_class($frame_to_add) == 'ffmpeg_frame' && $frame_to_add->hasValidResource())
            {
                array_push($this->_frames, $frame_to_add);
                $this->_saved = false;
                return $save === true ? $this->saveNow() : true;
            }
            return false;
        }
        
    }