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
	class VideoFormat_Flv extends VideoFormat
	{
		public function __construct($input_output_type, Config $config=null)
		{
			parent::__construct($input_output_type, $config);
			
			// TODO validate.
			$this->_temp_directory = $temp_directory;
			
//			default by forcing the audio codec to use mp3
			if($input_output_type === 'output')
			{
				$this->setAudioCodec('mp3')
					 ->setVideoCodec('flv1')
					 ->setFormat('flv');
			}
			
			$this->_restricted_audio_codecs = array('mp3');
			$this->_restricted_video_codecs = array('flv1');
			$this->_restricted_audio_sample_frequencies = array(44100, 22050, 11025);
		}
		
		public function updateFormatOptions()
		{
			parent::updateFormatOptions();
			
//			ffmpeg moans about audio sample frequencies on videos that aren't one of the following
//			audio sample rates. 44100, 22050, 11025
			if(empty($this->_format['audio_sample_frequency']) === true)
			{
				$audio_data = $this->_media_object->readAudioComponent();
				if(empty($audio_data['sample']['rate']) === true)
				{
					$this->setAudioSampleFrequency($this->_restricted_audio_sample_frequencies[0]);
				}
				else if(in_array($audio_data['sample']['rate'], $this->_restricted_audio_sample_frequencies) === false)
				{
					$current_sample_rate = $audio_data['sample']['rate'];
					if($current_sample_rate > $this->_restricted_audio_sample_frequencies[0])
					{
						$current_sample_rate = $this->_restricted_audio_sample_frequencies[0];
					}
					else
					{
						// TODO
						$current_sample_rate = $this->_restricted_audio_sample_frequencies[0];
					}
					$this->setAudioSampleFrequency($current_sample_rate);
				}
			}
			
//			assign a post process so that yamdil (http://yamdi.sourceforge.net/) injects the meta data into to the flv.
			$this->_media_object->registerOutputPostProcess(array($this, 'postProcessMetaData'));
			
			return $this;
		}
		
		/**
		 * Specifically for authomatic post processing of FLV output to inject metadata,
		 * however it can also be used as a standalone function call from the FLVFormat object.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param Media $media 
		 * @return Media
		 */
		public function postProcessMetaData(Media $media)
		{
//			set the yamdi input and output options.
			$output = $media->getMediaPath();
			$temp_output = $output.'.yamdi.flv';

//			build the yamdi process
			$yamdi_process = new ProcessBuilder('/opt/local/bin/yamdi', $this->_temp_directory);
			$exec = $yamdi_process
						  ->add('-i', $output)
						  ->add('-o')->add($temp_output)
						  ->add('-s')
						  ->add('-k')
						  ->getExecBuffer();
				
//			execute the process.
			$exec->setBlocking(true)
				 ->execute();
				
//			check for any yamdi errors
			if($exec->hasError() === true)
			{
				// TODO, log or exception not sure as the original file is ok.
			}
			else
			{
//				nope everything went ok. so delete ffmpeg file, and then rename yamdi file to that of the original.
				unlink($output);
				rename($temp_output, $output);
			}
			
			return $media;
		}
	}
