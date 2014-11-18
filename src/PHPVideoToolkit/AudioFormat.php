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
            
            if($this->_q_available === true)
            {
                $quality_command = '-q:a(:<stream_specifier>) <setting>';
                $quality_default_value = array();
            }
            else
            {
                $quality_command = '-qscale:a <setting>';
                $quality_default_value = null;
            }

            $this->_format = array_merge($this->_format, array(
                'disable_audio' => false,
                'audio_quality' => $quality_default_value,
                'audio_codec' => array(),
                'audio_bitrate' => array(),
                'audio_sample_frequency' => array(),
                'audio_channels' => null,
                'audio_volume' => null,
                'audio_filters' => null,
            ));
            $this->_format_to_command = array_merge($this->_format_to_command, array(
                'disable_audio'             => '-an',
                'audio_quality'             => $quality_command,
                'audio_codec'               => '-codec:a(:<stream_specifier>) <setting>',
                'audio_bitrate'             => '-b:a(:<stream_specifier>) <setting>',
                'audio_sample_frequency'    => '-ar(:<stream_specifier>) <setting>',
                'audio_channels'            => array(
                    'input' => '-request_channels <setting>',
                    'output' => '-ac(:<index>) <setting>',
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
         * @param  mixed $stream_specifier Either a string or integer. If string it can be in the following formats:
         *  stream_index -> "1"
         *  stream_type[:stream_index] -> "v" -> "v:1"
         *  p:program_id[:stream_index]
         *  #stream_id or i:stream_id
         *  m:key[:value]
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         * @throws \InvalidArgumentException If a codec is not found.
         * @throws \InvalidArgumentException If a codec is not available in the restricted codecs array.
         * @throws \InvalidArgumentException If the $stream_specifier value is not a valid stream specifier.
         */
        public function setAudioCodec($audio_codec, $stream_specifier=null)
        {
            $stream_specifier = $stream_specifier !== null ? $this->_validateStreamSpecifier($stream_specifier, get_class($this).'::setAudioCodec', array('integer'=>true, 'stream_type'=>false, 'program_id'=>false, 'stream_id'=>false, 'meta'=>false)) : self::DEFAULT_STREAM_SPECIFIER;

            if($audio_codec === null)
            {
                if($stream_specifier === self::DEFAULT_STREAM_SPECIFIER)
                {
                    $this->_format['audio_codec'] = array();
                }
                else if(isset($this->_format['audio_codec'][$stream_specifier]) === true)
                {
                    unset($this->_format['audio_codec'][$stream_specifier]);
                }
                return $this;
            }
            
//          validate the audio codecs that are available from ffmpeg.
            $codecs = array_keys($this->getCodecs('audio'));
//          special case for copy as it is not included in the codec list but is valid
            array_push($codecs, 'copy');
            
//          run a libmp3lame check as it require different mp3 codec
//          updated. thanks to Varon for providing the research
            if(in_array($audio_codec, array('mp3', 'libmp3lame')) === true)
            {
                $audio_codec = in_array('libmp3lame', $codecs) === true ? 'libmp3lame' : 'mp3';
            }
//          fix vorbis
            else if($audio_codec === 'vorbis' || $audio_codec === 'libvorbis')
            {
                $audio_codec = in_array('libvorbis', $codecs) === true ? 'libvorbis' : 'vorbis';
            }
//          fix acc
            else if($audio_codec === 'aac' || $audio_codec === 'libfdk_aac')
            {
                $audio_codec = in_array('libfdk_aac', $codecs) === true ? 'libfdk_aac' : 'aac';
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
            
            $this->_format['audio_codec'][$stream_specifier] = $audio_codec;
            return $this;
        }
        
        /**
         * Sets the audio bit rate for the format.
         *
         * @access public
         * @author Oliver Lillie
         * @param string $bitrate 
         * @param  mixed $stream_specifier Either a string or integer. If string it can be in the following formats:
         *  stream_index -> "1"
         *  stream_type[:stream_index] -> "v" -> "v:1"
         *  p:program_id[:stream_index]
         *  #stream_id or i:stream_id
         *  m:key[:value]
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         * @throws \InvalidArgumentException If the bitrate is not in one of the restricted bit rates, if any.
         * @throws \InvalidArgumentException If the $stream_specifier value is not a valid stream specifier.
         * @todo expand out the shorthand notations of bitrates
         */
        public function setAudioBitrate($bitrate, $stream_specifier=null)
        {
            $this->_blockSetOnInputFormat('audio bitrate');
            
            $stream_specifier = $stream_specifier !== null ? $this->_validateStreamSpecifier($stream_specifier, get_class($this).'::setAudioBitrate', array('integer'=>true, 'stream_type'=>false, 'program_id'=>false, 'stream_id'=>false, 'meta'=>false)) : self::DEFAULT_STREAM_SPECIFIER;

            if($bitrate === null)
            {
                if($stream_specifier === self::DEFAULT_STREAM_SPECIFIER)
                {
                    $this->_format['audio_bitrate'] = array();
                }
                else if(isset($this->_format['audio_bitrate'][$stream_specifier]) === true)
                {
                    unset($this->_format['audio_bitrate'][$stream_specifier]);
                }
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
            
            $this->_format['audio_bitrate'][$stream_specifier] = $bitrate;
            return $this;
        }
        
        /**
         * Sets the audio sample frequency for the audio format.
         *
         * @access public
         * @author Oliver Lillie
         * @param string $audio_sample_frequency 
         * @param  mixed $stream_specifier Either a string or integer. If string it can be in the following formats:
         *  stream_index -> "1"
         *  stream_type[:stream_index] -> "v" -> "v:1"
         *  p:program_id[:stream_index]
         *  #stream_id or i:stream_id
         *  m:key[:value]
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         * @throws \InvalidArgumentException If the sample frequency is not an integer value.
         * @throws \InvalidArgumentException If the sample frequency is less than 0
         * @throws \InvalidArgumentException If the sample frequency is not in one of the restricted sample frequencies, if any.
         * @throws \InvalidArgumentException If the $stream_specifier value is not a valid stream specifier.
         */
        public function setAudioSampleFrequency($audio_sample_frequency, $stream_specifier=null)
        {
            $stream_specifier = $stream_specifier !== null ? $this->_validateStreamSpecifier($stream_specifier, get_class($this).'::setAudioSampleFrequency') : self::DEFAULT_STREAM_SPECIFIER;

            if($audio_sample_frequency === null)
            {
                if($stream_specifier === self::DEFAULT_STREAM_SPECIFIER)
                {
                    $this->_format['audio_sample_frequency'] = array();
                }
                else if(isset($this->_format['audio_sample_frequency'][$stream_specifier]) === true)
                {
                    unset($this->_format['audio_sample_frequency'][$stream_specifier]);
                }
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
                
            $this->_format['audio_sample_frequency'][$stream_specifier] = $audio_sample_frequency;
            return $this;
        }
        
        /**
         * Sets the number of available audio channels. 
         *
         * @access public
         * @author Oliver Lillie
         * @param integer $channels One of the following integers; 0, 1, 2, 6.
         * @param  mixed $stream_specifier Either a string or integer. If string it can be in the following formats:
         *  stream_index -> "1"
         *  stream_type[:stream_index] -> "v" -> "v:1"
         *  p:program_id[:stream_index]
         *  #stream_id or i:stream_id
         *  m:key[:value]
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         * @throws \InvalidArgumentException If $channels value is not an integer.
         * @throws \InvalidArgumentException If $channels value is not one of the allowed values.
         * @throws \InvalidArgumentException If the $stream_specifier value is not a valid stream specifier.
         */
        public function setAudioChannels($channels, $stream_specifier=null)
        {
            if($this->_type === Format::INPUT && $stream_specifier !== null)
            {
                throw new \LogicException('It is not possible to set a stream specifier on an input format with setAudioChannels.');
            }

            if($this->_type === Format::OUTPUT)
            {
                $stream_specifier = $stream_specifier !== null ? $this->_validateStreamSpecifier($stream_specifier, get_class($this).'::setAudioChannels') : self::DEFAULT_STREAM_SPECIFIER;
            }

            if($channels === null)
            {
                if($this->_type === Format::OUTPUT)
                {
                    if($stream_specifier === self::DEFAULT_STREAM_SPECIFIER)
                    {
                        $this->_format['audio_channels'] = array();
                    }
                    else if(isset($this->_format['audio_channels'][$stream_specifier]) === true)
                    {
                        unset($this->_format['audio_channels'][$stream_specifier]);
                    }
                }
                else
                {
                    $this->_format['audio_channels'] = null;
                }
                return $this;
            }
            
            if(is_int($channels) === false)
            {
                throw new \InvalidArgumentException('The channels value must be an integer.');
            }
            else if(in_array($channels, array(0, 1, 2, 6)) !== false)
            {
                throw new \InvalidArgumentException('Unrecognised audio channels "'.$channels.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioChannels. The channels value must be one of the following values: 0, 1, 2, or 6.');
            }

            if($this->_type === Format::OUTPUT)
            {
                $this->_format['audio_channels'][$stream_specifier] = $channels;
            }
            else
            {
                $this->_format['audio_channels'] = $channels;
            }
            return $this;
        }
        
        /**
         * Sets the audio streams volumn level.
         *
         * @access public
         * @author Oliver Lillie
         * @param integer $volume The level of the volumn. Must be higher than or euqal to 0.
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         * @throws \InvalidArgumentException If $volume value is not an integer.
         * @throws \InvalidArgumentException If $volume is less than 0.
         */
        public function setVolume($volume)
        {
            if($volume === null)
            {
                $this->_format['audio_volume'] = null;
                return $this;
            }
            
            if(is_int($volume) === false)
            {
                throw new \InvalidArgumentException('The volumne value must be an integer.');
            }
            else if($volume < 0)
            {
                throw new \InvalidArgumentException('Unrecognised volume value "'.$volume.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setVolume. The value must be higher than or equal to 0.');
            }
            
            $this->_format['audio_volume'] = $volume;
            return $this;
        }
        
        /**
         * Sets the audio quality on a 0-100 scale.
         *
         * @access public
         * @author Oliver Lillie
         * @param mixed $quality Integer or Float. The quality level of the audio on a 0-100 scale.
         * @param  mixed $stream_specifier Either a string or integer. If string it can be in the following formats:
         *  stream_index -> "1"
         *  stream_type[:stream_index] -> "v" -> "v:1"
         *  p:program_id[:stream_index]
         *  #stream_id or i:stream_id
         *  m:key[:value]
         * @return PHPVideoToolkit\AudioFormat Returns the current object.
         * @throws \InvalidArgumentException If $qaulity value is not an integer or float.
         * @throws \InvalidArgumentException If $qaulity value does not eventually work out to be between 0-31.
         * @throws \InvalidArgumentException If the $stream_specifier value is not a valid stream specifier.
         */
        public function setAudioQuality($quality, $stream_specifier=null)
        {
            $this->_blockSetOnInputFormat('audio quality');
            
            if($stream_specifier !== null)
            {
                if(strpos($this->_format_to_command['quality'], ':index') === false)
                {
                    throw new \InvalidArgumentException('Your version of ffmpeg does not support stream specifiers. Please upgrade ffmpeg or remove the $stream_specifier argument.');
                }
                $stream_specifier = $this->_validateStreamSpecifier($stream_specifier, get_class($this).'::setAudioQuality', array('integer'=>true, 'stream_type'=>false, 'program_id'=>false, 'stream_id'=>false, 'meta'=>false));
            }
            else
            {
                $stream_specifier = self::DEFAULT_STREAM_SPECIFIER;
            }
            
            if($quality === null)
            {
                if(is_array($this->_format['audio_quality']) === true)
                {
                    if($stream_specifier === self::DEFAULT_STREAM_SPECIFIER)
                    {
                        $this->_format['audio_quality'] = array();
                    }
                    else if(isset($this->_format['audio_quality'][$stream_specifier]) === true)
                    {
                        unset($this->_format['audio_quality'][$stream_specifier]);
                    }
                }
                else
                {
                    $this->_format['audio_quality'] = null;
                }
                return $this;
            }
    
            if (is_int($quality) === false && is_float($quality) === false)
            {
                throw new \InvalidArgumentException('The volume value must be an integer or float value.');
            }
            
//          interpret quality into ffmpeg value
            $quality = 31 - round(($quality / 100) * 31);
            if($quality > 31 || $quality < 1)
            {
                throw new \InvalidArgumentException('Unrecognised quality "'.$quality.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setAudioQuality. The quality value must be between 0 and 100.');
            }
            
            if(is_array($this->_format['quality']) === true)
            {
                $this->_format['audio_quality'][$stream_specifier] = $qscale;
            }
            else
            {
                $this->_format['audio_quality'] = $qscale;
            }
            return $this;
        }
    }
