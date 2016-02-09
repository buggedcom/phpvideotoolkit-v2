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
     * Provides a base for audio related input/output format manipulation.
     *
     * @author Oliver Lillie
     */
    class AudioFormat extends Format
    {
        /**
         * A container for settable restricted bitrates.
         * @access protected
         */
        protected $_restricted_audio_bitrates;

        /**
         * A container for settable restricted sample frequencies
         * @access protected
         */
        protected $_restricted_audio_sample_frequencies;

        /**
         * A container for settable restricted audio codecs.
         * @access protected
         */
        protected $_restricted_audio_codecs;

        /**
         * Constructor
         *
         * @access public
         * @author Oliver Lillie
         * @param  constant $input_output_type Determines the input/output type of the Format. Either PHPVideoToolkit\Format::INPUT 
         *  or PHPVideoToolkit\Format::OUTPUT
         * @param  PHPVideoToolkit\Config $config The config object.
         */
        public function __construct($input_output_type=Format::OUTPUT, Config $config=null)
        {
            parent::__construct($input_output_type, $config);
            
            $this->_format = array_merge($this->_format, array(
                'disable_audio' => false,
                'audio_quality' => null,
                'audio_codec' => null,
                'audio_bitrate' => null,
                'audio_sample_frequency' => null,
                'audio_channels' => null,
                'audio_volume' => null,
                'audio_filters' => null,
            ));
            $this->_format_to_command = array_merge($this->_format_to_command, array(
                'disable_audio'             => '-an',
                'audio_quality'             => '-qscale:a <setting>',
                'audio_codec'               => '-acodec <setting>',
                'audio_bitrate'             => '-ab <setting>',
                'audio_sample_frequency'    => '-ar <setting>',
                'audio_channels'            => array(
                    'input' => '-request_channels <setting>',
                    'output' => '-ac <setting>',
                ),
                'audio_volume'              => '-af volume=<setting>',
            ));
            
            $this->_restricted_audio_bitrates = null;
            $this->_restricted_audio_sample_frequencies = null;
            $this->_restricted_audio_codecs = null;
        }
        
        /**
         * This is a hook function that is called when the PHPVideoToolkit\Media::_processOutputFormat function is run.
         * This allows the format to update any commands in itself depending on other functions called within the Media object. 
         *
         * @access public
         * @author Oliver Lillie
         * @param string &$save_path The save path of the output media.
         * @param  constant $overwrite The Media constant used to determine the overwrite status of the save. One of the 
         *  following constants:
         *  PHPVideoToolkit\Media::OVERWRITE_FAIL
         *  PHPVideoToolkit\Media::OVERWRITE_EXISTING
         *  PHPVideoToolkit\Media::OVERWRITE_UNIQUE
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         * @todo Implement audio filters.
         */
        public function updateFormatOptions(&$save_path, $overwrite)
        {
            parent::updateFormatOptions($save_path, $overwrite);
            
            // TODO expand the video_filters format data
            if(empty($this->_format['audio_filters']) === false)
            {
                
            }

            return $this;
        }
        
        /**
         * Adds an audio filter to the audio filters list to be applied to the audio.
         *
         * @access public
         * @author Oliver Lillie
         * @param AudioFilter $filter 
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         * @todo Implement
         */
        public function addAudioFilter(AudioFilter $filter)
        {
            $this->_blockSetOnInputFormat('audio filter');
            
            $this->_setFilter('audio_filters', $filter);
            
            return $this;
        }
        
        /**
         * Disables the audio stream of the input media in the output media.
         *
         * @access public
         * @author Oliver Lillie
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         * @throws \LogicException If the format input/output type is input.
         */
        public function disableAudio()
        {
            if($this->_type === 'input')
            {
                throw new \LogicException('Audio cannot be disabled on an input '.get_class($this).'.');
            }
            
            $this->_format['disable_audio'] = true;
            
            return $this;
        }
        
        /**
         * Enables the audio by disabling any previously set disableAudio call.
         *
         * @access public
         * @author Oliver Lillie
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         */
        public function enableAudio()
        {
            $this->_format['disable_audio'] = false;
            
            return $this;
        }
        
        /**
         * Sets the audio codec for the audio stream. The audio codec must be one of the codecs given from
         * PHPVideoToolkit\FfmpegParser::getCodecs or 'copy'. There are a few audio codecs that are automagically 
         * corrected depending on their availability on the current system. These codecs are:
         * - mp3 and libmp3lame
         * - vorbis and libvorbis
         * - acc and libfdk_aac
         *
         * @access public
         * @author Oliver Lillie
         * @param string $audio_codec 
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         * @throws \InvalidArgumentException If a codec is not found.
         * @throws \InvalidArgumentException If a codec is not available in the restricted codecs array.
         */
        public function setAudioCodec($audio_codec)
        {
            if($audio_codec === null)
            {
                $this->_format['audio_codec'] = null;
                return $this;
            }
            
//          validate the audio codecs that are available from ffmpeg.
            $codecs = array_keys($this->getCodecs('audio'));
//          special case for copy as it is not included in the codec list but is valid
            array_push($codecs, 'copy');
            
//          check that the codec exists...
            if(in_array($audio_codec, $codecs) === false)
            {
//              ...otherwise best guess with related codecs in order of performance.
//              https://trac.ffmpeg.org/wiki/Encode/HighQualityAudio
                $codecs_in_preference_order = false;
                if(in_array($audio_codec, array('mp3', 'libmp3lame', 'libshine')) === true)
                {
                    $codecs_in_preference_order = array('libmp3lame', 'libshine', 'mp3');
                }
//              fix vorbis
                else if(in_array($audio_codec, array('vorbis', 'libvorbis')) === true)
                {
                    $codecs_in_preference_order = array('libvorbis', 'vorbis');
                }
//              fix acc
                else if(in_array($audio_codec, array('libfdk_aac', 'libfaac', 'aac', 'libvo_aacenc')) === true)
                {
                    $codecs_in_preference_order = array('libfdk_aac', 'libfaac', 'aac', 'libvo_aacenc');
                }

                if($codecs_in_preference_order !== false){
                    $audio_codec = array_shift($codecs_in_preference_order);
                    while(in_array($audio_codec, $codecs) === false && count($codecs_in_preference_order) > 0){
                        array_push($codecs, $audio_codec);
                    }
                }
            }

            if(in_array($audio_codec, $codecs) === false)
            {
                throw new \InvalidArgumentException('Unrecognised audio codec "'.$audio_codec.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioCodec');
            }
            
//          now check the class settings to see if restricted pixel formats have been set and have to be obeyed
            if($this->_restricted_audio_codecs !== null)
            {
                if(in_array($audio_codec, $this->_restricted_audio_codecs) === false)
                {
                    throw new \InvalidArgumentException('The audio codec "'.$audio_codec.'" cannot be set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioCodec. Please select one of the following codecs: '.implode(', ', $this->_restricted_audio_codecs));
                }
            }
            
            $this->_format['audio_codec'] = $audio_codec;
            return $this;
        }
        
        /**
         * Sets the audio bit rate for the format.
         *
         * @access public
         * @author Oliver Lillie
         * @param string $bitrate 
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         * @throws \InvalidArgumentException If the bitrate is not in one of the restricted bit rates, if any.
         * @todo expand out the shorthand notations of bitrates
         */
        public function setAudioBitrate($bitrate)
        {
            $this->_blockSetOnInputFormat('audio bitrate');
            
            if($bitrate === null)
            {
                $this->_format['audio_bitrate'] = null;
                return $this;
            }
            
//          expand out any short hand
            if(preg_match('/^[0-9]+k$/', $bitrate) > 0)
            {
                // TODO make this exapnd out the kbs values
            }
            
//          now check the class settings to see if restricted audio bitrates have been set and have to be obeys
            if($this->_restricted_audio_bitrates !== null)
            {
                if(in_array($bitrate, $this->_restricted_audio_bitrates) === false)
                {
                    throw new \InvalidArgumentException('The bitrate "'.$bitrate.'" cannot be set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioBitrate. Please select one of the following bitrates: '.implode(', ', $this->_restricted_audio_bitrates));
                }
            }
            
            $this->_format['audio_bitrate'] = $bitrate;
            $this->setQualityVsStreamabilityBalanceRatio(NULL);
            return $this;
        }
        
        /**
         * Sets the audio sample frequency for the audio format.
         *
         * @access public
         * @author Oliver Lillie
         * @param string $audio_sample_frequency 
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         * @throws \InvalidArgumentException If the sample frequency is not an integer value.
         * @throws \InvalidArgumentException If the sample frequency is less than 0
         * @throws \InvalidArgumentException If the sample frequency is not in one of the restricted sample frequencies, if any.
         */
        public function setAudioSampleFrequency($audio_sample_frequency)
        {
            if($audio_sample_frequency === null)
            {
                $this->_format['audio_sample_frequency'] = null;
                return $this;
            }
            else if(is_integer($audio_sample_frequency) === false)
            {
                throw new \InvalidArgumentException('The audio sample frequency value must be an integer, '.gettype($audio_sample_frequency).' given.');
            }
            else if($audio_sample_frequency <= 0)
            {
                throw new \InvalidArgumentException('Unrecognised audio sample frequency "'.$format.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioSampleFrequency');
            }
            
//          now check the class settings to see if restricted audio audio sample frequencies have been set and have to be obeyed
            if($this->_restricted_audio_sample_frequencies !== null)
            {
                if(in_array($audio_sample_frequency, $this->_restricted_audio_sample_frequencies) === false)
                {
                    throw new \InvalidArgumentException('The audio sample frequency "'.$audio_sample_frequency.'" cannot be set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioSampleFrequency. Please select one of the following sample frequencies: '.implode(', ', $this->_restricted_audio_sample_frequencies));
                }
            }
                
            $this->_format['audio_sample_frequency'] = $audio_sample_frequency;
            return $this;
        }
        
        /**
         * Sets the number of available audio channels. 
         *
         * @access public
         * @author Oliver Lillie
         * @param integer $channels One of the following integers; 0, 1, 2, 6.
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         * @throws \InvalidArgumentException If $channels value is not an integer.
         * @throws \InvalidArgumentException If $channels value is not one of the allowed values.
         */
        public function setAudioChannels($channels)
        {
            if($channels === null)
            {
                $this->_format['audio_channels'] = null;
                return $this;
            }
            
            if(is_int($channels) === false)
            {
                throw new \InvalidArgumentException('The channels value must be an integer.');
            }
            else if(in_array($channels, array(0, 1, 2, 6)) === false)
            {
                throw new \InvalidArgumentException('Unrecognised audio channels "'.$channels.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioChannels. The channels value must be one of the following values: 0, 1, 2, or 6.');
            }

            $this->_format['audio_channels'] = $channels;
            return $this;
        }
        
        /**
         * Sets the audio streams volumn level.
         *
         * @access public
         * @author Oliver Lillie
         * @param integer $volume The level of the volumn. Must be higher than or euqal to 0.
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         * @throws \InvalidArgumentException If $volume value neither ends in 'dB' nor is an integer or float.
         * @throws \InvalidArgumentException If $volume is less than 0.
         */
        public function setVolume($volume)
        {
            if($volume === null)
            {
                $this->_format['audio_volume'] = null;
                return $this;
            }
            
            //Volume can also end in dB, and can be float as well as integer
            if(preg_match('/db$/i', $volume) === false && is_numeric($volume) === false)
            {
                throw new \InvalidArgumentException('The volume value must be an integer or float or end in "dB".');
            }
            //Make sure that volume is not less than 0 even if it ends in dB
            else if(preg_replace('/db$/i',"",$volume) < 0)
            {
                throw new \InvalidArgumentException('Unrecognised volume value "'.$volume.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVolume. The value must be higher than or equal to 0.');
            }
            
            $this->_format['audio_volume'] = str_replace('db', 'dB', $volume);
            return $this;
        }
        
        /**
         * Sets the audio quality on a 0-100 scale.
         *
         * @access public
         * @author Oliver Lillie
         * @param mixed $quality Integer or Float. The quality level of the audio on a 0-100 scale.
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         * @throws \InvalidArgumentException If $qaulity value is not an integer or float.
         * @throws \InvalidArgumentException If $qaulity value does not eventually work out to be between 0-31.
         */
        public function setAudioQuality($quality)
        {
            $this->_blockSetOnInputFormat('audio quality');
            
            if($quality === null)
            {
                $this->_format['audio_quality'] = null;
                return $this;
            }
            else if (is_int($quality) === false && is_float($quality) === false)
            {
                throw new \InvalidArgumentException('Audio quality value must be an integer or float value.');
            }
            
//          interpret quality into ffmpeg value
            $quality = 32 - round(($quality * 30/99) + 1);
            if($quality > 31 || $quality < 1)
            {
                throw new \InvalidArgumentException('Unrecognised quality "'.$quality.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioQuality. The quality value must be between 0 and 100.');
            }
            
            $this->_format['audio_quality'] = $quality;
            return $this;
        }
    }
