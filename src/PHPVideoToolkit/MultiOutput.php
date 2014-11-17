<?php
    
    /**
     * This file is part of the PHP Video Toolkit v2 package.
     *
     * @author Oliver Lillie (aka buggedcom) <publicmail@buggedcom.co.uk>
     * @license Dual licensed under MIT and GPLv2
     * @copyright Copyright (c) 2008-2014 Oliver Lillie <http://www.buggedcom.co.uk>
     * @package PHPVideoToolkit V2
     * @version 2.1.1
     * @uses ffmpeg http://ffmpeg.sourceforge.net/
     */
     
     namespace PHPVideoToolkit;
     
    /**
     * This class provides a way of getting multiple output from ffmpeg.
     *
     * @access public
     * @author Oliver Lillie
     * @package default
     */
    class MultiOutput implements \IteratorAggregate
    {
        protected $_output;
        protected $_config;
        protected $_default_output_format;

        /**
         * Constructor for MultiOutput object.
         *
         * @access public
         * @author Oliver Lillie
         * @param  string $output_path If provided then it is given as the initial output path.
         * @param  Format $output_format If provided then it is given as the initial output paths's output format object.
         * @param  Config $config The PHPVideoToolkit configuration options.
         */
        public function __construct(Config $config=null)
        {
            $this->_config = $config === null ? Config::getInstance() : $config;

            $this->_default_output_format = 'Format';
            $this->_output = array();
        }

        public function getIterator()
        {
            return new \ArrayIterator($this->_output);
        }

        /**
         * Returns output paths and formats for the desired ffmpeg output.
         *
         * @access public
         * @author Oliver Lillie
         * @return array Returns and array of path=>output format pairs.
         */
        public function getOutput()
        {
            return $this->_output;
        }

        public function setDefaultOutputFormat($format)
        {
            $this->_default_output_format = $format;
        }

        /**
         * Adds an output path and output format to the Output object.
         *
         * @access public
         * @author Oliver Lillie
         * @Muaram  string $output_path The output path of the desired generated output.
         * @param  Format $output_format The output Format object of the output format. If null is supplied then a best
         *  guess format object is generated and used.
         */
        public function addOutput($output_path, Format $output_format=null)
        {
            if(isset($this->_output[$output_path]) === true)
            {
                throw new \LogicException('Output for `'.$output_path.'` has already been given. Unable to set new output. If you wish to remove output please call PHPVideoToolkit\Ouput->removeOutput($output_path);.');
            }

            if($output_format === null)
            {
                $output_format = $this->_bestGuessOutputFormat($output_path);
            }
            $this->_output[$output_path] = $output_format;
        }

        /**
         * Removes output from the output path.
         *
         * @access public
         * @author Oliver Lillie
         * @param  string $output_path The output to remove.
         * @return void
         */
        public function removeOutput($output_path)
        {
            unset($this->_output[$output_path]);
        }

        /**
         * Returns the best guess output format object based on the given path.
         *
         * @access protected
         * @author Oliver Lillie
         * @param  string $path The output path of the resulting output.
         * @return object Returns an instance of a Format object or child class.
         */
        protected function _bestGuessOutputFormat($path)
        {
            $format = null;
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if(empty($ext) === false)
            {
                $format = Extensions::toBestGuessFormat($ext);
            }
            return $this->_getDefaultFormat('output', $this->_default_output_format, $format);
        }

        /**
         * Returns a format class set to the specific output/input type.
         *
         * @access protected
         * @author Oliver Lillie
         * @param string $type Either input for an input format or output for an output format.
         * @param string $class_name The class name of the Format instance to return.
         * @package Format Returns an instance of a Format object or child class.
         */
        protected function _getDefaultFormat($type, $default_class_name, $format)
        {
            // TODO replace with reference to Format::getFormatFor
            if(in_array($type, array('input', 'output')) === false)
            {
                throw new \InvalidArgumentException('Unrecognised format type "'.$type.'".');
            }
            
//          check the requested class exists
            $class_name = '\\PHPVideoToolkit\\'.$default_class_name.(empty($format) === false ? '_'.ucfirst(strtolower($format)) : '');
            if(class_exists($class_name) === false)
            {
                $requested_class_name = $class_name;
                $class_name = '\\PHPVideoToolkit\\'.$default_class_name;
                if(class_exists($class_name) === false)
                {
                    throw new \InvalidArgumentException('Requested default format class does not exist, "'.($requested_class_name === $class_name ? $class_name : $requested_class_name.'" and "'.$class_name.'"').'".');
                }
            }
            
//          check that it extends from the base Format class.
            if($class_name !== '\\PHPVideoToolkit\\Format' && is_subclass_of($class_name, '\\PHPVideoToolkit\\Format') === false)
            {
                throw new \LogicException('The class "'.$class_name.'" is not a subclass of \\PHPVideoToolkit\\Format.');
            }
            
            return new $class_name($type, $this->_config);
        }

    }
