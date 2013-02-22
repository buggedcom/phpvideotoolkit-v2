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
	abstract class ParserAbstract
	{
		protected $_program_path;
		protected $_temp_directory;
		protected $_cacher;
		static $_cache = array();
		
		public function __construct($program_path, $temp_directory)
		{
			$this->_program_path = $program_path;
			
			if(is_dir($temp_directory) === false)
			{
				throw new Exception('The temp directory does not exist or is not a directory.');
			}
			else if(is_readable($temp_directory) === false)
			{
				throw new Exception('The temp directory is not readable.');
			}
			else if(is_writable($temp_directory) === false)
			{
				throw new Exception('The temp directory is not writeable.');
			}
			$this->_temp_directory = $temp_directory;
			
			if($this->isAvailable() === false)
			{
				throw new Exception('FFmpeg appears to be unavailable on your system.');
			}
		}
		
		/**
		 * Sets a cacher object.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param CacheAbstract $cache_object 
		 * @return void
		 */
		public function setCacher(CacheAbstract $cache_object=null)
		{
			$this->_cacher = $cache_object;
		}
		
		/**
		 * Returns the cacher object.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return CacheAbstract
		 */
		public function getCacher()
		{
			return $this->_cacher;
		}
		
		/**
		 * Gets a value from the class cache and if not found then looks into
		 * the cache object.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $key 
		 * @param string $default_value 
		 * @return void
		 */
		protected function _cacheGet($key, $default_value=null)
		{
			if(isset(self::$_cache[$key]) === true)
			{
				return self::$_cache[$key];
			}
			else if(is_object($this->_cacher) === true)
			{
				return $this->_cacher->get($key, $default_value);
			}
			return $default_value;
		}
		
		/**
		 * Sets a value into the class cache and the cacher object.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $key 
		 * @param string $value 
		 * @param string $expiry 
		 * @return void
		 */
		protected function _cacheSet($key, $value, $expiry=null)
		{
			self::$_cache[$key] = $value;
			
			if(is_object($this->_cacher) === true)
			{
				return $this->_cacher->set($key, $value, $expiry);
			}
			
			return null;
		}
		
		/**
		 * Checks to see if ffmpeg is available.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param boolean $read_from_cache 
		 * @return boolean
		 */
		abstract public function isAvailable($read_from_cache=true);
	}
