<?php

    /**
     * This is a pure php emulation of the PHP module FFmpeg-PHP.
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
     * @see ffmpeg-php, 
     *      - @link http://ffmpeg-php.sourceforge.net/
     *      - @author Todd Kirby.
     *      - all phpdoc documentation is lifted directly from the ffmpeg-php docs
     */
    
    class ffmpeg_frame
    {
        
        private $_gd_resource   = null;
        private $_width         = null;
        private $_height        = null;
        private $_pts           = null;
        
        /**
         * Class Constructor
         * @param resource $gd_resource A GD image resource.
         * @param float $pts The frame presentation timestamp. Default value 0.0
         */
        function __construct($gd_resource, $pts=0.0)
        {
            $this->_gd_resource = $gd_resource;
            $this->_pts         = $pts;
            $this->_width       = imagesx($gd_resource);
            $this->_height      = imagesy($gd_resource);
        }
        
        function __destruct()
        {
            if(is_resource($this->_gd_resource))
            {
                imagedestroy($this->_gd_resource);
            }
        }
        
        /**
         * Determines if the resource supplied to the frame is valid.
         * @access public
         * @return integer
         */
        public function hasValidResource()
        {
            return is_resource($this->_gd_resource);
        }
        
        /**
         * Return the width of the frame.
         * @access public
         * @return integer
         */
        public function getWidth()
        {
            return $this->_width;
        }
        
        /**
         * Return the height of the frame.
         * @access public
         * @return integer
         */
        public function getHeight()
        {
            return $this->_height;
        }
        
        /**
         * Return the presentation time stamp of the frame.
         * @access public
         * @uses ffmpeg_frame::getPTS()
         * @return integer
         */
        public function getPresentationTimestamp()
        {
            return $this->getPTS();
        }
        
        /**
         * Return the presentation time stamp of the frame.
         * @access public
         * @return integer
         */
        public function getPTS()
        {
            return $this->_pts;
        }
        
        /**
         * Resize and optionally crop the frame. (Cropping is built into ffmpeg resizing so I'm providing it here for completeness.)
         * NOTE 1: Cropping is always applied to the frame before it is resized.
         * NOTE 2: Crop values must be even numbers.
         * @access public
         * @param integer $width New width of the frame (must be an even number).
         * @param integer $height New height of the frame (must be an even number).
         * @param integer $crop_top Remove [croptop] rows of pixels from the top of the frame.
         * @param integer $crop_bottom Remove [cropbottom] rows of pixels from the bottom of the frame.
         * @param integer $crop_left Remove [cropleft] rows of pixels from the left of the frame.
         * @param integer $crop_right Remove [cropright] rows of pixels from the right of the frame. 
         * @return boolean
         */
        public function resize($width, $height, $crop_top=false, $crop_bottom=false, $crop_left=false, $crop_right=false)
        {
//          are we cropping?
            if($crop_top !== false || $crop_bottom !== false || $crop_left !== false || $crop_right !== false)
            {
//              crop and check it went ok
                if(!$this->crop($crop_top, $crop_bottom, $crop_left, $crop_right))
                {
                    return false;
                }
            }
//          check the width and height
            if($width <= 0 || $height <= 0)
            {
                return false;
            }
//          now resize what we have
            $resize_resource = imagecreatetruecolor($width, $height);
//          copy the portion we want
            imagecopyresampled($resize_resource, $this->_gd_resource, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);
//          destroy the old crop resource to free up memory
            imagedestroy($this->_gd_resource);
//          save the new resource
            $this->_gd_resource = $resize_resource;
//          update the saved width and height
            $this->_width   = $width;
            $this->_height  = $height;
            return true;
        }
        
        /**
         * Crop the frame.
         * @access public
         * @param integer $crop_top Remove [croptop] rows of pixels from the top of the frame.
         * @param integer $crop_bottom Remove [cropbottom] rows of pixels from the bottom of the frame.
         * @param integer $crop_left Remove [cropleft] rows of pixels from the left of the frame.
         * @param integer $crop_right Remove [cropright] rows of pixels from the right of the frame. 
         * @return boolean
         */
        public function crop($crop_top=false, $crop_bottom=false, $crop_left=false, $crop_right=false)
        {
//          work out the newwidth and height and positions
            $w = $this->_width;
            $h = $this->_height;
            $x = 0;
            $y = 0;
            $x_bottom_chord = 0;
            if($crop_top !== false)
            {
                $x = $crop_top;
                $h -= $crop_top;
            }
            if($crop_bottom !== false)
            {
                $h -= $crop_bottom;
            }
            if($crop_left !== false)
            {
                $y = $crop_left;
                $w -= $crop_left;
            }
            if($crop_right !== false)
            {
                $w -= $crop_left;
            }
//          is the width and height greater than 0
            if($w < 0 || $h < 0)
            {
                return false;
            }
//          create the new image resource
            $crop_resource      = imagecreatetruecolor($w, $h);
//          copy the portion we want
            imagecopyresampled($crop_resource, $this->_gd_resource, 0, 0, $x, $y, $w, $h, $w, $h);
//          destroy the old resource to free up memory
            imagedestroy($this->_gd_resource);
//          save the new resource
            $this->_gd_resource = $crop_resource;
//          update the saved width and height
            $this->_width   = $w;
            $this->_height  = $h;
            return true;
        }
        
        /**
         * Returns a truecolor GD image of the frame.
         * @access public
         * @return integer
         */
        public function toGDImage()
        {
            return $this->_gd_resource;
        }
        
    }
    