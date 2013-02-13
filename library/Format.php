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
	 * @package default
	 */
	class Format extends FfmpegParser
	{
		protected $_format;
		protected $_type;
		
		protected $_format_to_command;
		
		protected $_additional_commands;
		protected $_removed_commands;
		
		public function __construct($ffmpeg_path, $temp_directory, $type='output')
		{
			parent::__construct($ffmpeg_path, $temp_directory);
			
			if($type === 'output')
			{
			}
			else if($type === 'input')
			{
			}
			else
			{
				throw new Exception('Unrecognised type "'.$type.'" set in \\PHPVideoToolkit\\Format::__construct');
			}
			
			$this->_additional_commands = array();
			$this->_removed_commands = array();
			
			$this->_format = array(
				'quality' => null,
				'format' => null,
				'strictness' => null,
				'preset_options_file' => null,
			);
			$this->_format_to_command = array(
				'quality' => '-q <setting>',
				'format'  => '-f <setting>',
				'strictness'  => '-strict <setting>',
				'preset_options_file'  => '-fpre <setting>',
			);
			
			$this->setType($type);
		}
		
		public function getFormatOptions()
		{
			return $this->_format;
		}
		
		/**
		 * Builds a returnable command string for the give options and additional commands.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return string
		 */
		public function getCommandString()
		{
			$commands = array();
			
			$mapped_commands = $this->_mapFormatToCommands();
			$commands = array_merge($commands, array_values($mapped_commands));
			
			$additional_commands = $this->_getAdditionalCommands();
			$commands = array_merge($commands, $additional_commands);
			
			return implode(' ', $commands);
		}
		
		/**
		 * Builds the additional commands.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @return array
		 */
		protected function _getAdditionalCommands()
		{
			$commands = array();
			
			if(empty($this->_additional_commands) === false)
			{
				foreach ($this->_additional_commands as $option => $value)
				{
					array_push($commands, $option.' '.escapeshellarg($value));
				}
			}
			
			return $commands;
		}
		
		/**
		 * Maps the supplied format options to those in the Format->_format_to_command array.
		 *
		 * @access protected
		 * @author Oliver Lillie
		 * @return array
		 */
		protected function _mapFormatToCommands()
		{
			$options = array();

			foreach ($this->_format as $option => $value)
			{
//				if the value is explicitly null or false we ignore it as it has not been set.
//				if the value is to be set but not be ignored then it should be set as an empty string, ie '';
				if($value === null || $value === false)
				{
					continue;
				}
				
				if(isset($this->_format_to_command[$option]) === false)
				{
					throw new Exception('Unable to map format option to command option as the command option does not exist in the map.');
				}
				
//				get the full command option string
				$full_command = $this->_format_to_command[$option];
				
//				if the command is an array, that means it has differing options depending on whether or not
//				this is an input or output format.
				if(is_array($full_command) === true)
				{
					$full_command = $full_command[$this->_type];
				}
				
//				now just the main command so we can ignore it if found in the additional supplied commands 
//				list of the list of commands to ignore.
				preg_match('/^([^\s]+)/', $full_command, $matches);
				$command = $matches[1];
				if(isset($this->_additional_commands[$command]) === true)
				{
					continue;
				}
				if(isset($this->_removed_commands[$command]) === true)
				{
					continue;
				}
				
//				otherwise if the value is an array, that means we have multiple options to replace into the command
				if(is_array($value) === true)
				{
//					note that multiple  values must already be escaped.
					$command = str_replace(array_keys($value), $value, $full_command);
				}
//				otherwise, it's jsut a <setting> that is to be replaced
				else
				{
					$command = str_replace('<setting>', escapeshellarg($value), $full_command);
				}
				
				$options[$option] = $command;
			}
			
			return $options;
		}
		
		/**
		 * Adds additional commands that will be added to the formatted command string
		 * that is returned from the format object. Any command options added here take
		 * precendence over those set in the Format->_format array
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $option 
		 * @param string $arg 
		 * @return void
		 */
		public function addCommand($option, $arg)
		{
			$this->_additional_commands[$option] = $arg;
		}
		
		public function removeCommand()
		{
			
		}
		
		/**
		 * Sets the format type, either input or output
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $type 
		 * @return void
		 */
		public function setType($type)
		{
//			validate input
			if(in_array($type, array('input', 'output')) === true)
			{
				$this->_type = $type;
				return $this;
			}
			
			throw new Exception('Unrecognised format "'.$format.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setFormat');
		}
		
		public function setPresetOptionsFile($preset_file_path)
		{
			$preset_file_path = realpath($preset_file_path);
			
			if(is_file($preset_file_path) === false)
			{
				throw new Exception('Preset options file "'.$preset_file_path.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setPresetOptionsFile does not exist.');
			}
			else if(is_readable($preset_file_path) === false)
			{
				throw new Exception('Preset preset options file "'.$preset_file_path.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setPresetOptionsFile is not readable.');
			}
			
			$this->_format['preset_options_file'] = $preset_file_path;
			return $this;
		}
		
		public function setStrictness($strictness)
		{
			if(in_array($strictness, array('very', 'strict', 'normal', 'unofficial', 'experimental')) === true)
			{
				$this->_format['strictness'] = $strictness;
				return $this;
			}
			
			throw new Exception('Unrecognised strictness "'.$strictness.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setStrictness');
		}
		
		public function setQuality($quality)
		{
			if($this->_type === 'input')
			{
				throw new Exception('The quality cannot be set on an input \\PHPVideoToolkit\\'.get_class($this).'::setQuality.');
			}
			
// 			interpret quality into ffmpeg value
			$quality = 31 - round(($quality / 100) * 31);
			if($quality > 31 || $quality < 1)
			{
				throw new Exception('Unrecognised quality "'.$quality.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setQuality');
			}
			
			$this->_format['quality'] = $quality;
			return $this;
		}
		
		public function setFormat($format)
		{
			// TODO work out what can be input and what can't be inputed
			
//			validate input
			$valid_formats = array_keys($this->getFormats());
			if(in_array($format, $valid_formats) === true)
			{
//				check to see if segmenting has been requested. If it has warn of the Media::split function instead.
				if($format === 'segment')
				{
					throw new Exception('You cannot set the format to segment, please use instead the function \\PHPVideoToolkit\\Media::segment.');
				}
					
				$this->_format['format'] = $format;
				return $this;
			}
			
			throw new Exception('Unrecognised format "'.$format.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setFormat');
		}
		
	}
