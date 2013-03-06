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
	abstract class CacheAbstract implements CacheInterface
	{
		protected $_cache_object;
		
		private $_key_prefix = 'phpvideotoolkit_v2/';
		
		final public function __construct(CacheInterface $cache_object)
		{
			$this->_cache_object = $cache_object;
		}
		
		final public function set($key, $value, $expiration=null)
		{
			$this->_cache[$key] = $value;
			
			return $this->_set($this->_key_prefix.$key, $value, $expiration);
		}
		
		final public function get($key, $default_value=null)
		{
			$key = $this->_key_prefix.$key;
			
			if($this->_isMiss($key) === true)
			{
				return $default_value;
			}
			return $this->_get($key);
		}
		
		abstract protected function _get($key);
		abstract protected function _isMiss($key);
		abstract protected function _set($key, $value, $expiration=null);
	}
