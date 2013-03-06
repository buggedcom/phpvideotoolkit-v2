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
	 * This class provides generic data parsing for the output from FFmpeg.
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @author Jorrit Schippers
	 * @package default
	 */
	class FfmpegProcessException extends Exception
	{
		protected $process;
		protected $exec;
		
		public function __construct($message = null, ExceBuffer $exec=null, FfmpegProcess $process=null, $code = 0, Exception $previous=null)
		{
			parent::__construct($message, $code, $previous);
			
			$this->process = $process;
			$this->exec = $exec;
		}
		
		final public function getFfmpegProcess()
		{
			return $this->process;
		}
		
		final public function getExecBuffer()
		{
			return $this->exec;
		}
	}
