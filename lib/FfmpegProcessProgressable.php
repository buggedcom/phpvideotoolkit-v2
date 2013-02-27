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

				$callback->attachFfmpegProcess($this, $this->_temp_directory);
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
		
		public function getOutput()
		{
			if($this->isCompleted() === false)
			{
				throw new Exception('Encoding has not yet started.');
			}
			
//			check for an error.
			if($this->hasError() === true)
			{
				throw new Exception('Encoding failed and an error was returned from ffmpeg. Error code '.$this->getErrorCode().' was returned the message (if any) was: '.$this->getLastSplit());
			}
			
//			get the output of the process and check for existence
			$output = $this->getOutputPath();
			if(empty($output) === true)
			{
				throw new Exception('Unable to find output for the process as it was not set.');
			}
			else if(is_file($output) === false)
			{
				throw new Exception('The output "'.$output.'", of the Ffmpeg process does not exist.');
			}
			else if(filesize($output) <= 0)
			{
				throw new Exception('The output "'.$output.'", of the Ffmpeg process is a 0 byte file. Something must have gone wrong however it wasn\'t reported as an error by FFmpeg.');
			}
			
//			get the media class from the output.
			$media_class = $this->findMediaClass($output);
			
//			create the object from the class name and return the new object.
			return new $media_class($output, null, $this->_binary_path, $this->_temp_directory);
		}
		
		public function findMediaClass($path)
		{
//			read the output to determine what it is so it can be post processed.
			try
			{
				$parser = new MediaParser($this->_binary_path, $this->_temp_directory);
				$output_information = $parser->getFileInformation($path, false);
			}
			catch(Exception $e)
			{
				throw new Exception('The output "'.$output.'", of the Ffmpeg process could not be read by MediaParser.', 0, $e);
			}
			
//			now we have the information switch between the types and create the return object.
			$class = 'Media';
			$type = $output_information['type'];
			switch($type)
			{
				case 'audio' :
				case 'video' :
				case 'image' :
					$class = '\\PHPVideoToolkit\\'.ucfirst(strtolower($type));
					break;
			}
			
			return $class;
		}

	}
