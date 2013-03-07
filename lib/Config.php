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
		protected $_temp_directory;
		protected $_qtfaststart;
		protected $_gif_transcoder;
		protected $_gifsicle;
		protected $_convert;

	    /**
	     * Get the Instance of self
	     *
	     * @return App_Config
	     */
	    public static function getInstance(array $options=array())
	    {
	        if(self::$instance === null)
			{
	            self::$instance = new self;
	        }

	        return self::$instance;
	    }

	    /**
	     * Class Constructor
	     *
	     * @param array $config
	     * @access private
	     */
	    public function __construct(array $options=array())
	    {
	        if(empty($options) === true)
			{
	            $options = array(
					'ffmpeg' 		 => 'ffmpeg',
					'ffprobe' 		 => 'ffprobe',
					'yamdi' 		 => null, //'yamdi', // http://yamdi.sourceforge.net/ for flv meta injection
					'qtfaststart' 	 => null, //'qt-faststart', // https://ffmpeg.org/trac/ffmpeg/wiki/UbuntuCompilationGuide#qt-faststart for fast streaming of mp4/h264 files.
					'temp_directory' => sys_get_temp_dir(),
					'gif_transcoder' => null,
					'gifsicle' => null,
					'convert' => null,
				);
	        }
	        $this->setConfig($options);
	    }

	    /**
	     * Set config options array
	     *
	     * @param array $options
	     * @access private
	     * @return App_Config
	     */
	    private function setConfig(array $options=array())
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
					
				case 'temp_directory' :
				
					$value = realpath($value);
					if(empty($value) === true || is_dir($value) === false)
					{
						throw new ConfigSetException('`temp_directory` "'.$value.'" does not exist or is not a directory.');
					}
					else if(is_readable($value) === false)
					{
						throw new ConfigSetException('`temp_directory` "'.$value.'" is not readable.');
					}
					else if(is_writable($value) === false)
					{
						throw new ConfigSetException('`temp_directory` "'.$value.'" is not writeable.');
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
	     * @param string $name
	     * @throws Exception
	     */
	    public function __get($key)
	    {
			if(isset($this->{'_'.$key}) === true)
			{
				return $this->{'_'.$key};
			}
			
//			TODO trigger error instead of just returning null
			return null;
//	        throw new Exception('Call to undefined property: '.$name);
	    }
	}
