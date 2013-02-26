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
	 
    class TempFile
    {
        protected $_delete_temp_registered = false; 
        protected $_do_not_remove = array(); 
        protected $_sid; 
        protected $_temp_directory; 
        protected $_time; 
		
        public $clean = true; 
		
		public function __construct($temp_directory, $id=null)
		{
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
			
			$this->_time = time();
			
			$this->_sid = uniqid((empty($id) === false ? $id.'_' : '').md5(__FILE__).'_');
		}
        
        /**
         * Is actually a private function as is registered to cleanup any temp files
         * created, but has to be public so that register_shutdown_function registers the file.
         * 
         * @access public
         * @return void
         * @author Oliver Lillie
         */
        public function _deleteTemp()
        {   
//          delete current temp files              
            $files = glob($this->_temp_directory.'temp'.DIRECTORY_SEPARATOR.'_temp_'.$this->_sid.'_*');  
            if(empty($files) === false)
            {
                foreach ($files as $file)
                {
                    if(in_array($file, self::$_do_not_remove) === false)
                    {
                        if(is_file($file) === true)
                        {
                         //   @unlink($file);
                        }
                    }
                }
            }
        }
        
        /**
         * Generates a temp filename
         *
         * @param string $extension 
         * @return void
         * @author Oliver Lillie
         */
        public function name($extension=false, $postfix=false)
        {   
            $extension = $extension === false ? '' : '.'.$extension;
            if($this->_sid === false)
            {
                $this->_sid = uniqid('');
            }   

            $id = '_temp_'.$this->_time.'_'.$this->_sid;
            if($postfix !== false)
            {
                $id .= $postfix;
            }
            else
            {
                $id .= '_'.uniqid('');
            }
            
            return $id.$extension;
        }
         
        /**
         * Creates a temporary file that is only available for the duration of the
         * script execution. It is automatically cleaned up by an end function.
         * It can handle the following types of input to store as temporary files.
         * - gd image resources
         * - uploaded files
         * - filenames
         * - remote files starting with http
         * - and a data string
         * 
         * @access public
         * @param string $data 
         * @param boolean $extension 
         * @param boolean $clean 
         * @return void
         * @author Oliver Lillie
         */
        public function file($data, $extension=false, $clean=true)
        {    
            $result = false;
            $filename = $this->_temp_directory.DIRECTORY_SEPARATOR.self::name($extension);
            if(is_resource($data) === true)
            { 
//              if the data is a resource, output it
//                 TODO add other types
                switch(get_resource_type($data))
                {   
                    case 'gd' : 
                        $func = 'image'.($extension === false ? 'gd' : ($extension === 'jpg' ? 'jpeg' : $extension));
                        $result = call_user_func($func, $data, $filename);
                        break;
                }             
            }
            else if(is_uploaded_file($data) === true)
            { 
//              data is an uploaded file path 
                $result = @move_uploaded_file($data, $filename) === true;
            }
            else if((strpos($data, '.') === 0 || strpos($data, '/') === 0) && strlen($data) < 150 && is_file($data) === true)
            { 
//              data is an uploaded file path 
                $result = @copy($data, $filename) === true;
            }
            else if($data !== false)
            {
//              data is a string
                $result = file_put_contents($filename, $data);
            }
            else
            {
                $result = true;
            }
            if($result !== false)
            {    
                if($clean === true)
                {
                    if($this->_delete_temp_registered === false)
                    {
                        $this->_delete_temp_registered = true;
                        register_shutdown_function(array($this, '_deleteTemp'));
                    }
                }
                else
                {
                    array_push($this->_do_not_remove, $filename);
                }
                return $filename;
            }
            return false;
        } 
    }