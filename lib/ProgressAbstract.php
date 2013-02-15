<?php
	
	/**
	 * This file is part of the PHP Video Toolkit v2 package.
	 *
	 * @author Oliver Lillie (aka buggedcom) <publicmail@buggedcom.co.uk>
	 * @license Dual licensed under MIT and GPLv2
	 * @copyright Copyright (c) 2008 Oliver Lillie <http://www.buggedcom.co.uk>
	 * @package PHPVideoToolkit V2
	 * @version 2.0.0.a
	 * @uses ffmpeg http://ffmpeg.sourceforge.net/
	 */
	 
	 namespace PHPVideoToolkit;

	/**
	 * @access public
	 * @author Oliver Lillie
	 * @package default
	 */
	 abstract class ProgressHelper implements HelperInterface
	 {
	     protected $_duration = null;
	     protected $_rate;
	     protected $_format;
	     protected $_total_size;
	     protected $_current_size;
	     protected $_current_time;
	     protected $_last_output_time = null;
	     protected $_percent = 0;
	     protected $_remaining = null;
	     protected $_media_parser;
	     protected $_callback;
		 
	     public function __construct($callback, MediaParserAbstract $media_parser)
	     {
	         $this->_callback = $callback;
			 
	         $this->_media_parser = $media_parser;
	     }

	     public function transcodeCallback($channel, $content)
	     {
	         $progress = $this->parseProgress($content);

	         if(is_array($progress) === true)
			 {
	             call_user_func_array($this->_callback, $progress);
	         }
	     }

	     public function open($processing_file_path)
	     {
	         $format = $this->_media_probe_parser->getFormat($processing_file_path);

	         if($format === null || count($format) === 0 || isset($format['size']) === false)
			 {
	             throw new \RuntimeException('Unable to probe format for ' . $pathfile);
	         }

	         $this->_format = $format;
	         $this->_total_size = $format['size'] / 1024;
	         $this->_duration = $format['duration'];
	     }

	     /**
	      * @param string $progress A ffmpeg stderr progress output
	      * @return array the progressinfo array or null if there's no progress available yet.
	      */
	     public function parseProgress($progress)
	     {
	         $matches = array();

	         if (preg_match('/size=(.*?) time=(.*?) /', $progress, $matches) !== 1)
			 {
	             return null;
	         }

	         $current_timecode = new Timecode($matches[2], Timecode::INPUT_FORMAT_TIMECODE);
			 
	         $current_time = microtime(true);
	         $current_size = trim(str_replace('kb', '', strtolower(($matches[1]))));
	         $percent = max(0, min(1, $current_timecode->total_seconds / $this->_duration));

	         if($this->_last_output_time !== null)
			 {
	             $delta = $current_time - $this->_last_output_time;
	             $delta_size = $current_size - $this->_current_size;
	             $rate = $delta_size * $delta;
	             if($rate > 0)
				 {
	                 $total_duration = $this->_total_size / $rate;
	                 $this->_remaining = floor($total_duration - ($total_duration * $percent));
	                 $this->_rate = floor($rate);
	             }
				 else
				 {
	                 $this->_remaining = 0;
	                 $this->_rate = 0;
	             }
	         }

	         $this->_percent = floor($percent * 100);
	         $this->_last_output_time = $current_time;
	         $this->_current_size = (int) $current_size;
	         $this->_current_time = $current_duration;

	         return $this->getProgressInfo();
	     }

	     public function getProgressData()
	     {
	         return array(
	             'percent' => $this->_percent,
	             'remaining' => $this->_remaining,
	             'rate' => $this->_rate
	         );
	     }
	 }
