<?php
    
    /**
     * This file is part of the PHP Video Toolkit v2 package.
     *
     * @author Oliver Lillie (aka buggedcom) <publicmail@buggedcom.co.uk>
     * @license Dual licensed under MIT and GPLv2
     * @copyright Copyright (c) 2008-2014 Oliver Lillie <http://www.buggedcom.co.uk>
     * @package PHPVideoToolkit V2
     * @version 2.1.7-beta
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
        protected $_config;

        protected $_key_prefix = 'phpvideotoolkit_v2';
        
        public function __construct(Config $config=null)
        {
            $this->_config = $config === null ? Config::getInstance() : $config;
            if($this->isAvailable() === false)
            {
                $class_name = get_class($this);
                throw new Exception('The cache driver `'.substr($class_name, strrpos($class_name, '_')+1).'` is not available on your system.');
            }
        }
        
        public function set($key, $value, $expiration=null)
        {
            $this->_cache[$key] = $value;
            
            return $this->_set($this->_key_prefix.'_'.$key, $value, $expiration);
        }
        
        public function get($key, $default_value=null)
        {
            $key = $this->_key_prefix.'_'.$key;
            
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
