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
	 * 		- @link http://code.google.com/p/php-reader/
	 * 		- @author Sven Vollbehr <svollbehr@gmail.com>
	 *	  	- @license http://code.google.com/p/php-reader/wiki/License New BSD License
	 * @see ffmpeg-php, 
	 * 		- @link http://ffmpeg-php.sourceforge.net/
	 * 		- @author Todd Kirby.
	 * 		- all phpdoc documentation is lifted directly from the ffmpeg-php docs
	 */
	
	if(!defined('DS'))
	{
		define('DS', DIRECTORY_SEPARATOR);
	}
	
	class ffmpeg_movie
	{
		
		private $_frame_index 	= 1;
		private $_toolkit 		= null;
		private $_media_data 	= null;
		private $_php_reader 	= null;
		private $_path_to_media = null;
		private $_tmp_directory = null;
		
		/**
		 * Class Constructor
		 * @param string $path_to_media The path to the media file you want to use.
		 * @param string $persistent (not used in this class - but exists to emulate the ffmpeg-php module)
		 * @param string $tmp_directory The temp directory to which to work from. (This is only required by this class
		 * 	and not by ffmpeg-php so some minor hacking of your scripts may need to be done). (remember the trailing slash)
		 */
		function __construct($path_to_media, $persistent=false, $tmp_directory='/tmp/')
		{
// 			store the media path
			$this->_path_to_media = $path_to_media;
			$this->_tmp_directory = $tmp_directory;
// 			init PHPVideoToolkit class
			require_once dirname(dirname(dirname(__FILE__))).DS.'phpvideotoolkit.php5.php';
			$this->_toolkit = new PHPVideoToolkit($tmp_directory);
			$this->_toolkit->on_error_die = false;
// 			set the input
			$this->_toolkit->setInputFile($path_to_media);
			$this->_media_data = $this->_toolkit->getFileInfo();
// 			print_r($this->_media_data);
		}
		
		/**
		 * Access and returns the id3 information using getID3.
		 * @access private
		 * @return boolean true if the information was able to be retrieved, false if not
		 */
		private function _getPHPReader()
		{
			if($this->_php_reader === null)
			{
				$this->_php_reader = -1;
				$php_reader = dirname(__FILE__).DS.'php-reader'.DS.'src'.DS.'ID3v1.php';
				if(is_file($php_reader))
				{
					require_once $php_reader;
					try 
					{
						$this->_php_reader = new ID3v1($this->_path_to_media);
					} 
					catch (Exception $e) 
					{
						return false;
					}
					return true;
				}
			}
			return $this->_php_reader !== -1;
		}
		
		/**
		 * Return the duration of a movie or audio file in seconds.
		 * @access public
		 * @return integer
		 */
		public function getDuration()
		{
			return $this->_media_data['duration']['seconds'];
		}
		
		/**
		 * Return the number of frames in a movie or audio file.
		 * @access public
		 * @return integer
		 */
		public function getFrameCount()
		{
			return $this->hasVideo() ? $this->_media_data['video']['frame_count'] : -1;
		}
		
		/**
		 * Return the frame rate of a movie in fps.
		 * @access public
		 * @return integer
		 */
		public function getFrameRate()
		{
			return $this->hasVideo() ? $this->_media_data['video']['frame_rate'] : -1;
		}
		
		/**
		 * Return the path and name of the movie file or audio file.
		 * @access public
		 * @return string
		 */
		public function getFilename()
		{
			return basename($this->_path_to_media);
		}
		
		/**
		 * Makes checks and returns the id3 element value.
		 * @access private
		 * @return mixed string | -1 
		 */
		private function _getPHPReaderElement($element)
		{
			if($this->hasAudio())
			{
				if($this->_getPHPReader() && isset($this->_php_reader->{$element}))
				{
					return $this->_php_reader->{$element};
				}
			}
			return -1;
		}
		
		/**
		 * Return the comment field from the movie or audio file.
		 * Returns -1 on failure.
		 * @access public
		 * @return mixed array | -1
		 */
		public function getComment()
		{
			return $this->_getPHPReaderElement('comment');
		}
		
		/**
		 * Return the title field from the movie or audio file.
		 * Returns -1 on failure.
		 * @access public
		 * @return string
		 */
		public function getTitle()
		{
			return $this->_getPHPReaderElement('title');
		}
		
		/**
		 * Return the copyright field from the movie or audio file.
		 * @access public
		 * @return string
		 */
		public function getCopyright()
		{
			return $this->_getPHPReaderElement('copyright');
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
			return $this->_getPHPReaderElement('artist');
		}
		
		/**
		 * Return the album ID3 field from an mp3 file.
		 * @access public
		 * @return string
		 */
		public function getAlbum()
		{
			return $this->_getPHPReaderElement('album');
		}
		
		/**
		 * Return the genre ID3 field from an mp3 file.
		 * @access public
		 * @return string
		 */
		public function getGenre()
		{
			return $this->_getPHPReaderElement('genre');
		}
		
		/**
		 * Return the track ID3 field from an mp3 file.
		 * @access public
		 * @return integer
		 */
		public function getTrackNumber()
		{
			return $this->_getPHPReaderElement('track');
		}
		
		/**
		 * Return the year ID3 field from an mp3 file.
		 * @access public
		 * @return integer
		 */
		public function getYear()
		{
			return $this->_getPHPReaderElement('year');
		}
		
		/**
		 * Return the height of the movie in pixels.
		 * @access public
		 * @return integer
		 */
		public function getFrameHeight()
		{
			return $this->hasVideo() && isset($this->_media_data['video']['dimensions']) ? $this->_media_data['video']['dimensions']['height'] : -1;
		}
		
		/**
		 * Return the width of the movie in pixels.
		 * @access public
		 * @return integer
		 */
		public function getFrameWidth()
		{
			return $this->hasVideo() && isset($this->_media_data['video']['dimensions']) ? $this->_media_data['video']['dimensions']['width'] : -1;
		}
		
		/**
		 * Return the pixel format of the movie.
		 * @access public
		 * @return mixed string | -1
		 */
		public function getPixelFormat()
		{
			return $this->hasVideo() ? $this->_media_data['video']['pixel_format'] : -1;
		}
		
		/**
		 * Return the pixel aspect ratio of the movie
		 * @access public
		 * @return integer
		 */
		public function getPixelAspectRatio()
		{
			return -1; 
		}
		
		/**
		 * Return the bit rate of the movie or audio file in bits per second.
		 * @access public
		 * @return integer
		 */
		public function getBitRate()
		{
			return isset($this->_media_data['bitrate']) ? $this->_media_data['bitrate'] : -1;
		}
		
		/**
		 * Return the bit rate of the video in bits per second.
		 * NOTE: This only works for files with constant bit rate.
		 * @access public
		 * @return integer
		 */
		public function getVideoBitRate()
		{
			return $this->hasVideo() && isset($this->_media_data['video']['bitrate']) ? $this->_media_data['video']['bitrate'] : -1;
		}
		
		/**
		 * Return the audio bit rate of the media file in bits per second.
		 * @access public
		 * @return integer
		 */
		public function getAudioBitRate()
		{
			return $this->hasAudio() && isset($this->_media_data['audio']['bitrate']) ? $this->_media_data['audio']['bitrate'] : -1;
		}
		
		/**
		 * Return the audio sample rate of the media file in bits per second.
		 * @access public
		 * @return integer
		 */
		public function getAudioSampleRate()
		{
			return $this->hasAudio() && isset($this->_media_data['audio']['sample_rate']) ? $this->_media_data['audio']['sample_rate'] : -1;
		}
		
		/**
		 * Return the name of the video codec used to encode this movie as a string.
		 * @access public
		 * @param boolean $return_all If true it will return all audio codecs found.
		 * @return mixed string | array
		 */
		public function getVideoCodec($return_all=false)
		{
			return $this->hasVideo() ? $this->_media_data['video']['codec'] : -1;
		}
		
		/**
		 * Return the name of the audio codec used to encode this movie as a string.
		 * @access public
		 * @param boolean $return_all If true it will return all audio codecs found.
		 * @return mixed string | array
		 */
		public function getAudioCodec()
		{
			return $this->hasAudio() ? $this->_media_data['audio']['codec'] : -1;
		}
		
		/**
		 * Return the number of audio channels in this movie as an integer.
		 * @access public
		 * @return integer
		 */
		public function getAudioChannels()
		{
			if($this->hasAudio())
			{
				if($this->_getPHPReader() && isset($this->_getid3_data['audio']) && isset($this->_getid3_data['audio']['channels']))
				{
					return $this->_getid3_data['audio']['channels'];
				}
				return 1;
			}
			return 0;
		}
		
		/**
		 * Return boolean value indicating whether the movie has an audio stream.
		 * @access public
		 * @return boolean
		 */
		public function hasAudio()
		{
			return isset($this->_media_data['audio']);
		}
		
		/**
		 * Return boolean value indicating whether the movie has a video stream.
		 * @access public
		 * @return boolean
		 */
		public function hasVideo()
		{
			return isset($this->_media_data['video']);
		}
		
		/**
		 * Returns a frame from the movie as an ffmpeg_frame object. 
		 * Returns false if the frame was not found.
		 * @access public
		 * @return mixed boolean | ffmpeg_frame
		 */
		public function getFrame($frame_number=false)
		{
			if(!$this->hasVideo())
			{
				return false;
			}
			$this->_toolkit->reset(true);
			require_once dirname(__FILE__).DS.'ffmpeg_frame.php';
			if(!$frame_number)
			{
				$frame_number = $this->_frame_index;
				$this->_frame_index += 1;
			}
			else
			{
				$this->_frame_index = $frame_number;
			}
// 			check the frame required exists in the video
			if($frame_number > $this->getFrameCount())
			{
				return false;
			}
// 			work out the exact frame to take
			$frame_rate = $this->getFrameRate();
// 			generate a unique name
			$tmp_name	= $this->_toolkit->unique().'-%index.jpg';
// 			extract the frame
// 			print_r(array($frame_number, $frame_rate, '%ft'));
			$this->_toolkit->extractFrame($frame_number, $frame_rate, '%ft');
			$this->_toolkit->setOutput($this->_tmp_directory, $tmp_name, PHPVideoToolkit::OVERWRITE_EXISTING);
			$result = $this->_toolkit->execute(false, true);
// 			check the image has been outputted
// 			print_r(array($this->_toolkit->getLastError(), $this->_toolkit->getLastCommand()));
// 			print_r(array($this->_toolkit->getLastCommand()));
// 			print_r(array($tmp_name, $this->_toolkit->getLastOutput()));
			if($result !== PHPVideoToolkit::RESULT_OK)
			{
				return false;
			}
// 			load the frame into gd
			$temp_output = array_shift(array_flip($this->_toolkit->getLastOutput()));
			$gd_img = imagecreatefromjpeg($temp_output);
// 			delete the temp image
			unlink($temp_output);
// 			return the ffmpeg frame instance
			$ffmpeg_frame_time = $this->_toolkit->formatTimecode($frame_number, '%ft', '%hh:%mm:%ss.%ms', $frame_rate);
			return new ffmpeg_frame($gd_img, $ffmpeg_frame_time);
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
			$frame_rate 	= $this->getFrameRate();
// 			work out the next frame
			$current_second = floor($frame_number/$frame_rate);
			$excess			= $frame_number-($seconds * $frame_rate);
			$frames_to_next = $frame_rate-$excess;
			$this->_frame_index += $frames_to_next;
// 			get the frame
			return $this->getFrame();
		}
		
		/**
		 * Return the current frame index.
		 * @access public
		 * @return integer
		 */
		public function getFrameNumber()
		{
			return $this->_frame_index;
		}
		
	}
	