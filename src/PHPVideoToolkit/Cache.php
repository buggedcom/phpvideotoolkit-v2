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
     * Small wrapper for creating a cacher driver based on the given config settings.
     *
     * @author Oliver Lillie
     */
    class Cache
    {
        /**
         * Creates a singleton instance of a caching driver from the given PHPVideoToolkit\Config settings.
         *
         * @access public
         * @static
         * @author: Oliver Lillie
         * @param  PHPVideoToolkit\Config $config The config object.
         * @return PHPVideoToolkit\CacheAbstract Returns the cacher object.
         */
        public static function getCacher(Config $config)
        {
            static $cachers = array();
            if(isset($cachers[$config->cache_driver]) === true)
            {
                return $cachers[$config->cache_driver];
            }
            $class = '\PHPVideoToolkit\Cache_'.$config->cache_driver;
            return $cachers[$config->cache_driver] = new $class($config);
        }
    }
