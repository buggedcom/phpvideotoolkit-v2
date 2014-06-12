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
     * PHPVideoToolkit's caching driver for storing the cache inside the specified temp directory.
     *
     * @author Oliver Lillie
     */
    class Cache_InTempDirectory extends CacheAbstract
    {
        /**
         * Determines if this caching driver is available on the current system.
         *
         * @access public
         * @author Oliver Lillie
         * @return boolean Returns true if the driver is available, false otherwise.
         */
        public function isAvailable()
        {
            $dir = $this->_config->temp_directory;
            if(is_dir($dir) === false)
            {
                return false;
            }
            if(is_writable($dir) === false)
            {
                return false;
            }

            $cache_dir = $dir.'/'.$this->_key_prefix.'_cache';
            if(is_dir($cache_dir) === false && mkdir($cache_dir, 0770) === false)
            {
                return false;
            }
            return true;
        }

        /**
         * Creates the file path prefix for a cache file based off the given key.
         *
         * @access protected
         * @author Oliver Lillie
         * @param  string $key The cache key string.
         * @return string Returns the generated file path prefix.
         */
        protected function _getFilePathPrefix($key)
        {
            $dir = $this->_config->temp_directory;
            return $dir.'/'.$this->_key_prefix.'_cache/'.md5($key).'_';
        }

        /**
         * Returns the file path for the given key. Returning the most recent version and clearing old cache keys.
         *
         * @access protected
         * @author Oliver Lillie
         * @param  string $key The cache key string.
         * @return string Returns the found file path for the given cache key.
         */
        protected function _getFile($key)
        {
            $file_prefix = $this->_getFilePathPrefix($key);
            $matches = glob($file_prefix.'*');
            if(empty($matches) === false)
            {
                natsort($matches);
                $cur_time = time();
                $oldest = 0;
                foreach ($matches as $path)
                {
                    $name = basename($path);
                    $parts = explode('_', $name);
                    if($parts[1] < $cur_time)
                    {
                        @unlink($path);
                    }
                    else if($parts[1] > $oldest)
                    {
                        $oldest = $parts[1];
                    }
                }
                if($oldest > 0)
                {
                    return $file_prefix.$oldest;
                }
            }
            return null;
        }

        /**
         * Returns the unserialized contents for the given cache key if the file exists. 
         * Otherwise returns null.
         *
         * @access protected
         * @author Oliver Lillie
         * @param  string $key The cache key string.
         * @return mixed Returns null if the key does not exist, otherwise returns a mixed value depending on what has been stored.
         */
        protected function _get($key)
        {
            $file = $this->_getFile($key);
            if(is_file($file) === true)
            {
                $data = file_get_contents($file);
                return unserialize($data);
            }
            return null;
        }
        
        /**
         * Determines if the cache for the given key exists.
         *
         * @access protected
         * @author Oliver Lillie
         * @param  string $key The cache key string.
         * @return boolean Returns true if the key cache exists, otherwise false.
         */
        protected function _isMiss($key)
        {
            return !is_file($this->_getFile($key));
        }
        
        /**
         * Sets data to the given cache key with an optional expiration date.
         *
         * @access protected
         * @author Oliver Lillie
         * @param  string $key The cache key string.
         * @param  mixed $value The data to be stored by the cache driver.
         * @param  mixed $expiration Integer timestamp if the data is too expire, otherwise null as the cache defaults to expire in 1 hour.
         * @return boolean Returns true if the cache was saved to file.
         */
        protected function _set($key, $value, $expiration=null)
        {
            if($expiration === null)
            {
                $expiration = 3600;
            }

            $file = $this->_getFilePathPrefix($key).(time()+$expiration);
            $result = file_put_contents($file, serialize($value), LOCK_EX);
            @chmod($filename, 0660); 
            return $result;
        }
    }
