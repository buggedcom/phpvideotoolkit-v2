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
     * @author Jorrit Schippers
     * @package default
     */
    class Config
    {
        /**
         * PlaceHolder for self (Singlton)
         *
         * @var App_Config
         */
        public static $instance = null;
        
        protected $_ffmpeg;
        protected $_ffprobe;
        protected $_yamdi;
        protected $_qtfaststart;
        protected $_temp_directory;
        protected $_gif_transcoder;
        protected $_gif_transcoder_convert_use_dither;
        protected $_gif_transcoder_convert_dither_order;
        protected $_gif_transcoder_convert_use_coalesce;
        protected $_gif_transcoder_convert_use_map;
        protected $_gifsicle;
        protected $_convert;
        protected $_php_exec_infinite_timelimit;
        protected $_force_enable_qtfaststart;
        protected $_force_enable_flv_meta;
        protected $_cache_driver;

        /**
         * Get the Instance of self
         *
         * @return App_Config
         */
        public static function getInstance()
        {
            if(self::$instance === null)
            {
                self::$instance = new self;
            }

            return self::$instance;
        }

        /**
         * Constructs and merges the given config data with the defaul settings.
         *
         * @access public
         * @author: Oliver Lillie
         * @param  array $options An array of key=>value pairs to set into the config object.
         * @param  boolean $set_as_default If true (default false) then this instance of the Config object is set
         *  as the default configuration instance as returned by Config::getInstance.
         */
        public function __construct(array $options=array(), $set_as_default=false)
        {
            $default_options = array(
                'ffmpeg'         => 'ffmpeg',
                'ffprobe'        => 'ffprobe',
                'yamdi'          => null, //'yamdi', // http://yamdi.sourceforge.net/ for flv meta injection
                'qtfaststart'    => null, //'qt-faststart', // https://ffmpeg.org/trac/ffmpeg/wiki/UbuntuCompilationGuide#qt-faststart for fast streaming of mp4/h264 files.
                'temp_directory' => sys_get_temp_dir(),
                'gif_transcoder' => null,
                'gif_transcoder_convert_use_dither'     => true,
                'gif_transcoder_convert_dither_order'   => 'o8x8,8',
                'gif_transcoder_convert_use_coalesce'   => true,
                'gif_transcoder_convert_use_map'        => false,
                'gifsicle'       => null,
                'convert'        => null,
                'php_exec_infinite_timelimit' => true,
                'force_enable_qtfaststart'    => false,
                'force_enable_flv_meta'       => true,
                'cache_driver'   => 'Null',
            );
            $this->_setConfig(array_merge($default_options, $options));

            if($set_as_default === true)
            {
                $this->setAsDefaultInstance();
            }
        }

        /**
         * Sets the config object as the default config instance so the config object does not need to be
         * supplied in the constructor to all the PHPVideoToolkit objects.
         *
         * @access public
         * @author: Oliver Lillie
         */
        public function setAsDefaultInstance()
        {
            self::$instance = $this;
        }

        /**
         * Set config options array
         *
         * @param array $options
         * @access private
         * @return Config
         */
        private function _setConfig(array $options=array())
        {
            foreach ($options as $key => $value)
            {
                $this->{$key} = $value;
            }
            
            return $this;
        }

        /**
         * Magic method get
         *
         * This get's triggerd if there is a call made to an undefined property in
         * the App_Config instance or subInstance, so we throw an Exception
         *
         * @param string $name
         * @throws Exception
         */
        public function __set($key, $value)
        {
            switch($key)
            {
                case 'ffmpeg' :
                case 'ffprobe' :
                case 'yamdi' :
                case 'qtfaststart' :
                case 'gifsicle' :
                case 'convert' :
                
                    if($value !== null)
                    {
                        if(strpos($value, '/') !== 0)
                        {
                            try
                            {
                                $value = Binary::locate($value);
                            }
                            catch(BinaryLocateException $e)
                            {
                                throw new ConfigSetException('Unable to locate the '.$value.' binary. Please specify the full path instead.');
                            }
                        }
                    }
                    
                    $this->{'_'.$key} = $value;
                    
                    return;
                    
                case 'gif_transcoder' :
                    
                    if(in_array($value, array('gifsicle', 'convert', 'php', null)) === false)
                    {
                        throw new ConfigSetException('Unrecognised gif transcoder engine.');
                    }
                
                    $this->{'_'.$key} = $value;
                    
                    return;

                case 'cache_driver' :

                    $class = '\PHPVideoToolkit\Cache_'.$value;
                    if(class_exists($class) === false)
                    {
                        throw new ConfigSetException('Unrecognised cache driver engine. The cache class must be within the PHPVideoToolkit namespace and be prefixed by `Cache_`.');
                    }
                    if(is_subclass_of($class, '\PHPVideoToolkit\CacheAbstract') === false)
                    {
                        throw new ConfigSetException('Unrecognised cache driver engine. The cache driver provider must inherit from \PHPVideoToolkit\CacheAbstract.');
                    }
                
                    $this->{'_'.$key} = $value;
                    
                    return;
                    
                case 'gif_transcoder_convert_dither_order' :

                    if(preg_match('/o[0-9]+x[0-9]+,[0-9]+/', $value) === 0)
                    {
                        throw new ConfigSetException('Unrecognised dither order. Please enter in the following format: oNxN,N where N are numerics.');
                    }
                    $this->{'_'.$key} = $value;
                    
                    return;
                    
                case 'force_enable_qtfaststart' :
                case 'php_exec_infinite_timelimit' :
                case 'force_enable_flv_meta' :
                case 'gif_transcoder_convert_use_dither' :
                case 'gif_transcoder_convert_use_coalesce' :
                case 'gif_transcoder_convert_use_map' :
                    
                    if(in_array($value, array(true, false)) === false)
                    {
                        throw new ConfigSetException('Unrecognised '.$key.' value. It must be a boolean value, either true or false.');
                    }
                
                    $this->{'_'.$key} = $value;
                    
                    return;
                    
                case 'temp_directory' :
                
                    $original_value = $value;
                    $value = realpath($value);
                    if(empty($value) === true || is_dir($value) === false)
                    {
                        throw new ConfigSetException('`temp_directory` "'.$original_value.'" does not exist or is not a directory.');
                    }
                    else if(is_readable($value) === false)
                    {
                        throw new ConfigSetException('`temp_directory` "'.$original_value.'" is not readable.');
                    }
                    else if(is_writable($value) === false)
                    {
                        throw new ConfigSetException('`temp_directory` "'.$original_value.'" is not writeable.');
                    }
                    
                    $this->{'_'.$key} = $value;
                    
                    return;
            }
            
            throw new ConfigSetException('Setting undefined configuration property: '.$key);
        }

        /**
         * Magic method get
         *
         * This get's triggerd if there is a call made to an undefined property in
         * the App_Config instance or subInstance, so we throw an Exception
         *
         * @param string $key
         * @return mixed
         */
        public function __get($key)
        {
            if(isset($this->{'_'.$key}) === true)
            {
                return $this->{'_'.$key};
            }
            return null;
        }

        /**
         * Magic method set
         *
         * Determines if a property is set on the object.
         *
         * @param string $key
         * @return boolean
         */
        public function __isset($key)
        {
            return property_exists($this, '_'.$key) === true;
        }
    }
