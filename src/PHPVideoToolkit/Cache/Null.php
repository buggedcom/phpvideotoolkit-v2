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
     * PHPVideoToolkit's caching driver for a blackhole cache driver. Meaning that the cache functionality can be used but nothing ever stored.
     *
     * @author Oliver Lillie
     */
    class Cache_Null extends CacheAbstract
    {
        /**
         * Determines if this caching driver is available on the current system.
         * Returns true.
         *
         * @access public
         * @author Oliver Lillie
         * @return boolean Returns true.
         */
        public function isAvailable()
        {
            return true;
        }

        /**
         * Returns null.
         *
         * @access protected
         * @author Oliver Lillie
         * @param  string $key The cache key string.
         * @return mixed Returns null if the key does not exist, otherwise returns a mixed value depending on what has been stored.
         */
        protected function _get($key)
        {
            return null;
        }
        
        /**
         * Returns true.
         *
         * @access protected
         * @author Oliver Lillie
         * @param  string $key The cache key string.
         * @return boolean Returns true.
         */
        protected function _isMiss($key)
        {
            return true;
        }
        
        /**
         * Returns null.
         *
         * @access protected
         * @author Oliver Lillie
         * @param  string $key The cache key string.
         * @param  mixed $value The data to be stored by the cache driver.
         * @param  mixed $expiration Integer timestamp if the data is too expire, otherwise null as the cache defaults to expire in 1 hour.
         * @return boolean Returns null.
         */
        protected function _set($key, $value, $expiration=null)
        {
            return null;
        }
    }
