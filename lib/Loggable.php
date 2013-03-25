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
	class Loggable
	{
		protected $_logger;
		
		public function setLogger(Logger $logger)
		{
			$this->_logger = $logger;
			
			return $this;
		}
		
		public function getLogger()
		{
			return $this->_logger;
		}
		
		public function log($message)
		{
			if($this->_logger)
			{
				$this->_logger->log($message);
			}
			
			return $this;
		}
	}
