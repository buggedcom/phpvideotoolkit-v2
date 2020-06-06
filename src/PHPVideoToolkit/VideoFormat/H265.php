<?php
    
    /**
     * This file is part of the PHP Video Toolkit v2 package.
     *
     * @author Ivan Sinkarenko (aka ivany4)
     * @license MIT
     * @package PHPVideoToolkit V2
     * @version 2.2.6
     * @uses ffmpeg http://ffmpeg.sourceforge.net/
     */
     
    namespace PHPVideoToolkit;

    /**
     * @access public
     * @author ivany4
     * @package default
     */
    class VideoFormat_H265 extends VideoFormat
    {
        public function __construct($input_output_type=Format::OUTPUT, Config $config=null)
        {
            parent::__construct($input_output_type, $config);
            
            $this->_format = array_merge($this->_format, array(
                'h265_preset' => null,
                'h265_tune' => null,
                'h265_profile' => null,
            ));
            $this->_format_to_command = array_merge($this->_format_to_command, array(
                'h265_preset' => '-preset <setting>',
                'h265_tune' => '-tune <setting>',
                'h265_profile' => '-profile:v <setting>',
            ));
            
            if($input_output_type === 'output')
            {
                $this->setAudioCodec('aac')
                     ->setVideoCodec('libx265')
                     ->setFormat('mp4');
            }
            
            $this->_restricted_video_codecs = array('libx265', 'hevc', 'h265');
        }
        
        public function setH265Preset($preset=null)
        {
            $this->_blockSetOnInputFormat('h265 preset');
            
            if($preset === null)
            {
                $this->_format['h265_preset'] = null;
                return $this;
            }
            
            if(in_array($preset, array('ultrafast', 'superfast', 'veryfast', 'faster', 'fast', 'medium', 'slow', 'slower', 'veryslow', 'placebo')) === false)
            {
                throw new \InvalidArgumentException('Unrecognised h265 preset "'.$preset.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setH265Preset');
            }
            
            $this->_format['h265_preset'] = $preset;
            return $this;
        }
        
        public function setH265Tune($tune=null)
        {
            $this->_blockSetOnInputFormat('h265 tune');
            
            if($tune === null)
            {
                $this->_format['h265_tune'] = null;
                return $this;
            }
            
            if(in_array($tune, array('grain', 'psnr', 'ssim', 'fastdecode', 'zerolatency')) === false)
            {
                throw new \InvalidArgumentException('Unrecognised h265 tune "'.$preset.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setH265Tune');
            }
            
            $this->_format['h265_tune'] = $tune;
            return $this;
        }
        
        public function setH265Profile($profile=null)
        {
            $this->_blockSetOnInputFormat('h265 profile');
            
            if($profile === null)
            {
                $this->_format['h265_profile'] = null;
                return $this;
            }
            
            // Compatible profiles are listed in ITU-T H265 Standard, Annex A
            // https://www.itu.int/rec/dologin_pub.asp?lang=e&id=T-REC-H.265-201802-S!!PDF-E&type=items
            // Also, exact values can be found here: https://x265.readthedocs.io/en/default/cli.html?highlight=profile#profile-level-tier
            if(in_array($profile, array('main', 'main-intra', 'mainstillpicture', 'msp', 'main444-8', 'main444-intra', 'main444-stillpicture',
                                        'main10', 'main10-intra', 'main422-10', 'main422-10-intra', 'main444-10', 'main444-10-intra',
                                        'main12', 'main12-intra', 'main422-12', 'main422-12-intra', 'main444-12', 'main444-12-intra')) === false)
            {
                throw new Exception('Unrecognised h265 profile "'.$profile.'" set in \\PHPVideoToolkit\\'.get_class($this).'::setH265Profile');
            }
            
            $this->_format['h265_profile'] = $profile;
            return $this;
        }
    }
