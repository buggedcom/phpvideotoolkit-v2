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
	 * undocumented class
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @package default
	 */
	class FfmpegProcessProgressable extends FfmpegProcess 
	{
		private $_progress_callbacks;
		
		public function __construct($binary_path, $temp_directory)
		{
			parent::__construct($binary_path, $temp_directory);
			
			$this->_progress_callbacks = array();
		}
		
		public function attachProgressHandler($callback)
		{
			if(is_object($callback) === true)
			{
				if(is_subclass_of($callback, 'PHPVideoToolkit\ProgressHandlerAbstract') === false)
				{
					throw new Exception('If supplying an object to attach as a progress handler, that object must inherit from ProgressHandlerAbstract.');
				}
				
				$callback->attachFfmpegProcess($this);
			}
			else if(is_callable($callback) === false)
			{
				throw new Exception('The progress handler must either be a class that extends from ProgressHandlerAbstract or a callable function.');
			}
			
			array_push($this->_progress_callbacks, $callback);
		}
		
		public function _executionCallbackRunner()
		{
	        foreach($this->_progress_callbacks as $callback)
			{
				if(is_object($callback) === true)
				{
					$callback->callback();
				}
	            else
				{
					call_user_func($callback, $this);
				}
	        }
		}
		
		public function execute($callback=null)
		{
			if($callback !== null)
			{
				if(is_callable($callback) === false)
				{
					throw new Exception('Callback is not callable.');
				}

				$this->attachProgressHandler($callback);
			}
			
			if(empty($this->_progress_callbacks) === false)
			{
				$callback = array($this, '_executionCallbackRunner');
			}

			$this->getExecBuffer()
				 ->setBlocking(false)
				 ->execute($callback);
			
			return $this;
		}
		
	}
