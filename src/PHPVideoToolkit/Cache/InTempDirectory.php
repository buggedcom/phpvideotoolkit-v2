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
    class Cache_InTempDirectory extends CacheAbstract
    {
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

        protected function _getFilePathPrefix($key)
        {
            $dir = $this->_config->temp_directory;
            return $dir.'/'.$this->_key_prefix.'_cache/'.md5($key).'_';
        }

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
        
        protected function _isMiss($key)
        {
            return $this->_getFile($key) === null;
        }
        
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
