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
     * This is a container class for determining which ffmpeg parser class to use to be able to correctly parser the data 
     * returned by the ffmpeg that is installed on the system.
     *
     * @author Oliver Lillie
     */
    class FfmpegParser
    {
        protected $_parser;
        protected $_config;
        
        /**
         * Constructor
         *
         * @access public
         * @author: Oliver Lillie
         * @param  Config $config The config object.
         */
        public function __construct(Config $config=null)
        {
            $this->_config = $config === null ? Config::getInstance() : $config;
            $this->_parser = null;
        }
        
        /**
         * Gets the specific ffmpeg parser to use based upon the format data.
         *
         * @access public
         * @author Oliver Lillie
         * @return mixed Returns either PHPVideoToolkit\FfmpegParserFormatsArgumentOnly or PHPVideoToolkit\FfmpegParserGeneric 
         *  depending on what information is return in the format data.
         */
        protected function _getParser()
        {
            $parser = new Parser($this->_config);
            $format_data = $parser->getRawFormatData();
            if(strpos($format_data, 'Codecs:') !== false)
            {
                $this->_parser = new FfmpegParserFormatsArgumentOnly($this->_config);
            }
            else
            {
                $this->_parser = new FfmpegParserGeneric($this->_config);
            }

            return $this->_parser;
        }
        
        /**
         * Calls any method in the contained $_parser class.
         *
         * @access public
         * @author Oliver Lillie
         * @param string $name 
         * @param string $arguments 
         * @return mixed
         * @throws \BadMethodCallException if the function does not exist in the parser.
         */
        public function __call($name, $arguments)
        {
            if($this->_parser === null)
            {
                $this->_getParser();
            }
            
            if(method_exists($this->_parser, $name) === true)
            {
                return call_user_func_array(array($this->_parser, $name), $arguments);
            }
            else
            {
                throw new \BadMethodCallException('`'.$name.'` is not a valid parser function.');
            }
        }
    }
