<?php

    /**
     * This is a pure php emulation of the PHP module FFmpeg-PHP.
     * NOTE: Please note whenever possible you should use ffmpeg-php as it is much more efficient than this pure PHP emulation.
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
     * @uses php-reader
     *      - @link http://code.google.com/p/php-reader/
     *      - @author Sven Vollbehr <svollbehr@gmail.com>
     *      - @license http://code.google.com/p/php-reader/wiki/License New BSD License
     * @see ffmpeg-php, 
     *      - @link http://ffmpeg-php.sourceforge.net/
     *      - @author Todd Kirby.
     *      - all phpdoc documentation is lifted directly from the ffmpeg-php docs
     */
    
    if(!defined('DS'))
    {
        define('DS', DIRECTORY_SEPARATOR);
    }
    
    class ffmpeg_movie
    {
        
        private $_frame_index     = -1;
        private $_toolkit         = null;
        private $_config          = null;
        private $_meta            = null;
        private $_video_component = null;
        private $_audio_component = null;
        
        /**
         * Class Constructor
         * @param string $path_to_media The path to the media file you want to use.
         * @param string $persistent (not used in this class - but exists to emulate the ffmpeg-php module)
         */
        function __construct($video_path, $persistent=null)
        {
            $this->_toolkit = new \PHPVideoToolkit\Video($video_path, \PHPVideoToolkit\Config::getInstance());
        }
        
        /**
         * Stores the meta data from the toolkit class.
         *
         * @return stdClass object
         * @author Oliver Lillie
         */
        protected function _getMetaData()
        {
            if($this->_meta === null)
            {
                $this->_meta = (object) $this->_toolkit->readGlobalMetaData();
            }
            return $this->_meta;
        }
        
        /**
         * Stores the video component data from the toolkit class.
         *
         * @return stdClass object or boolean false if there is no video component.
         * @author Oliver Lillie
         */
        protected function _getVideoComponent()
        {
            if($this->_video_component === null)
            {
                if($this->_toolkit->readHasVideo() === false)
                {
                    $this->_video_component = false;
                }
                else
                {
                    $this->_video_component = (object) $this->_toolkit->readVideoComponent();
                }
            }
            return $this->_video_component;
        }
        
        /**
         * Stores the audio component data from the toolkit class.
         *
         * @return stdClass object or boolean false if there is no audio component.
         * @author Oliver Lillie
         */
        protected function _getAudioComponent()
        {
            if($this->_audio_component === null)
            {
                if($this->_toolkit->readHasVideo() === false)
                {
                    $this->_audio_component = false;
                }
                else
                {
                    $this->_audio_component = (object) $this->_toolkit->readAudioComponent();
                }
            }
            return $this->_audio_component;
        }
        
        /**
         * On clone the PHPVideoToolkit\Video is duplicated.
         *
         * @return void
         * @author Oliver Lillie
         */
        public function __clone()
        {
            $this->_toolkit = clone $this->_toolkit;
        }
        
        /**
         * Destructor.
         *
         * @return void
         * @author Oliver Lillie
         */
        public function __destruct()
        {
            $this->_video = null;
        }
        
        /**
         * Return the duration of a movie or audio file in seconds.
         * @access public
         * @return integer
         */
        public function getDuration()
        {
            return $this->_toolkit->readDuration()->seconds;
        }
        
        /**
         * Return the number of frames in a movie or audio file.
         * @access public
         * @return integer
         */
        public function getFrameCount()
        {
            $video = $this->_getVideoComponent();
            if($video === false)
            {
                return false;
            }
            return $video->frames['total'];
        }
        
        /**
         * Return the frame rate of a movie in fps.
         * @access public
         * @return integer
         */
        public function getFrameRate()
        {
            $video = $this->_getVideoComponent();
            if($video === false)
            {
                return false;
            }
            return $video->frames['rate'];
        }
        
        /**
         * Return the path and name of the movie file or audio file.
         * @access public
         * @return string
         */
        public function getFilename()
        {
            return basename($this->_toolkit->getMediaPath());
        }
        
        /**
         * Return the comment field from the movie or audio file.
         * Returns an empty string on failure.
         * @access public
         * @return mixed array | -1
         */
        public function getComment()
        {
            $meta_data = $this->_getMetaData();
            return isset($meta_data->comment) === true ? $meta_data->comment : '';
        }
        
        /**
         * Return the title field from the movie or audio file.
         * Returns -1 on failure.
         * @access public
         * @return string
         */
        public function getTitle()
        {
            $meta_data = $this->_getMetaData();
            return isset($meta_data->title) === true ? $meta_data->title : '';
        }
        
        /**
         * Return the copyright field from the movie or audio file.
         * @access public
         * @return string
         */
        public function getCopyright()
        {
            $meta_data = $this->_getMetaData();
            return isset($meta_data->copyright) === true ? $meta_data->copyright : '';
        }
        
        /**
         * Return the author field from the movie or the artist ID3 field from an mp3 file.
         * @uses ffmpeg_movie::getArtist();
         * @access public
         * @return string
         */
        public function getAuthor()
        {
            return $this->getArtist();
        }
        
        /**
         * Return the artist ID3 field from an mp3 file.
         * @access public
         * @return string
         */
        public function getArtist()
        {
            $meta_data = $this->_getMetaData();
            return isset($meta_data->artist) === true ? $meta_data->artist : '';
        }
        
        /**
         * Return the album ID3 field from an mp3 file.
         * @access public
         * @return string
         */
        public function getAlbum()
        {
            $meta_data = $this->_getMetaData();
            return isset($meta_data->album) === true ? $meta_data->album : '';
        }
        
        /**
         * Return the genre ID3 field from an mp3 file.
         * @access public
         * @return string
         */
        public function getGenre()
        {
            $meta_data = $this->_getMetaData();
            return isset($meta_data->genre) === true ? $meta_data->genre : '';
        }
        
        /**
         * Return the track ID3 field from an mp3 file.
         * @access public
         * @return integer
         */
        public function getTrackNumber()
        {
            $meta_data = $this->_getMetaData();
            return isset($meta_data->track) === true ? $meta_data->track : '';
        }
        
        /**
         * Return the year ID3 field from an mp3 file.
         * @access public
         * @return integer
         */
        public function getYear()
        {
            $meta_data = $this->_getMetaData();
            return isset($meta_data->year) === true ? $meta_data->year : (isset($meta_data->date) === true ? $meta_data->date : '');
        }
        
        /**
         * Return the height of the movie in pixels.
         * @access public
         * @return integer
         */
        public function getFrameHeight()
        {
            $video = $this->_getVideoComponent();
            if($video === false)
            {
                return false;
            }
            return $video->dimensions['height'];
        }
        
        /**
         * Return the width of the movie in pixels.
         * @access public
         * @return integer
         */
        public function getFrameWidth()
        {
            $video = $this->_getVideoComponent();
            if($video === false)
            {
                return false;
            }
            return $video->dimensions['width'];
        }
        
        /**
         * Return the pixel format of the movie.
         * @access public
         * @return mixed string | -1
         */
        public function getPixelFormat()
        {
            $video = $this->_getVideoComponent();
            if($video === false)
            {
                return false;
            }
            return $video->pixel_format;
        }
        
        /**
         * Return the bit rate of the movie or audio file in bits per second.
         * @access public
         * @return integer
         */
        public function getBitRate()
        {
            return $this->_toolkit->readBitrate();
        }
        
        /**
         * Return the bit rate of the video in bits per second.
         * NOTE: This only works for files with constant bit rate.
         * @access public
         * @return integer
         */
        public function getVideoBitRate()
        {
            $video = $this->_getVideoComponent();
            if($video === false)
            {
                return false;
            }
            return $video->bitrate;
        }
        
        /**
         * Return the audio bit rate of the media file in bits per second.
         * @access public
         * @return integer
         */
        public function getAudioBitRate()
        {
            $audio = $this->_getAudioComponent();
            if($audio === false)
            {
                return false;
            }
            return $audio->bitrate;
        }
        
        /**
         * Return the audio sample rate of the media file in bits per second.
         * @access public
         * @return integer
         */
        public function getAudioSampleRate()
        {
            $audio = $this->_getAudioComponent();
            if($audio === false)
            {
                return false;
            }
            return $audio->sample['rate'];
        }
        
        /**
         * Return the name of the video codec used to encode this movie as a string.
         * @access public
         * @param boolean $return_all If true it will return all audio codecs found.
         * @return mixed string | array
         */
        public function getVideoCodec($return_all=false)
        {
            $video = $this->_getVideoComponent();
            if($video === false)
            {
                return false;
            }
            return $video->codec['name'];
        }
        
        /**
         * Return the name of the audio codec used to encode this movie as a string.
         * @access public
         * @param boolean $return_all If true it will return all audio codecs found.
         * @return mixed string | array
         */
        public function getAudioCodec()
        {
            $audio = $this->_getAudioComponent();
            if($audio === false)
            {
                return false;
            }
            return $audio->codec['name'];
        }
        
        /**
         * Return the number of audio channels in this movie as an integer.
         * @access public
         * @return integer
         */
        public function getAudioChannels()
        {
            $audio = $this->_getAudioComponent();
            if($audio === false)
            {
                return false;
            }
            return $audio->channels;
        }
        
        /**
         * Return boolean value indicating whether the movie has an audio stream.
         * @access public
         * @return boolean
         */
        public function hasAudio()
        {
            return $this->_getAudioComponent() !== false;
        }
        
        /**
         * Return boolean value indicating whether the movie has a video stream.
         * @access public
         * @return boolean
         */
        public function hasVideo()
        {
            return $this->_getVideoComponent() !== false;
        }
        
        /**
         * Returns a frame from the movie as an ffmpeg_frame object. 
         * Returns false if the frame was not found.
         * @access public
         * @return mixed boolean | ffmpeg_frame
         */
        public function getFrame($frame_number=null)
        {
//          check that we have video.
            if($this->hasVideo() === false)
            {
                return false;
            }
            
//          check that the frame number is not less than or equal to 0 as the argument is not
//          zero indexed and cannot get a negative rframe number.
            if($frame_number <= 0)
            {
                return false;
            }
            
//          update the current frame index
            if($frame_number === null)
            {
                $this->_frame_index += 1;
                $frame_number = $this->_frame_index;
            }
            else
            {
//              zero indexed
                $frame_number -= 1;
                $this->_frame_index = $frame_number;
            }
            
//          check the frame required exists in the video
            if($frame_number > $this->getFrameCount())
            {
                return false;
            }

//          get the timecode of the frame to extract;
            $timecode = \PHPVideoToolkit\Timecode::parseTimecode($frame_number, '%fn', $this->getFrameRate());
            
//          get the temp directory for the output.
            $config = \PHPVideoToolkit\Config::getInstance();
            $temp = new \PHPVideoToolkit\TempFile($config->temp_directory);
            $output_path = $temp->file(false, 'png');
            
//          perform the extraction
            $output = $this->_toolkit->extractFrame($timecode)
                            ->save($output_path, null, \PHPVideoToolkit\Media::OVERWRITE_UNIQUE);
            
//          then convert the image to GD resource and tidy the temp file.
            $gd_img = $output->toGdImage();
            unlink($output->getOutput()->getMediaPath());
            
//          return the ffmpeg_frame object.
            require_once dirname(__FILE__).'ffmpeg_frame.php';
            return new ffmpeg_frame($gd_img, $timecode->getTimecode('%sf.%ms'));
        }
        
        /**
         * Note; this doesn't behave exactly as ffmpeg_movie, this will get the first frame
         * of the next second in the movie.
         * Returns the next key frame from the movie as an ffmpeg_frame object. 
         * Returns false if the frame was not found.
         * @uses ffmpeg_movie::getFrame();
         * @access public
         * @return mixed boolean | ffmpeg_frame
         */
        public function getNextKeyFrame()
        {
//          get the timecode of the frame to extract;
            $timecode = \PHPVideoToolkit\Timecode::parseTimecode($this->_frame_index, '%fn', $this->getFrameRate());
            $timecode->seconds += 1;
            
//          get the frame
            return $this->getFrame($timecode->frames);
        }
        
        /**
         * Return the current frame index.
         * @access public
         * @return integer
         */
        public function getFrameNumber()
        {
            return $this->_frame_index < 0 ? 1 : $this->_frame_index+1;
        }
        
    }
    