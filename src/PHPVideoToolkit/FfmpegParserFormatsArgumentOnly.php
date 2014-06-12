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
     * Extends FfmpegParserAbstract using the arguments only method rather.
     * 
     * @author Oliver Lillie
     */
    class FfmpegParserFormatsArgumentOnly extends FfmpegParserAbstract
    {
        /**
         * Returns the raw data returned from ffmpeg about the available supported codecs.
         *
         * @access public
         * @author Oliver Lillie
         * @param boolean $read_from_cache If true and the data exists within a cache then that data is used. If false
         *  then the data is re-read from ffmpeg.
         * @return string Returns the raw buffer data from ffmpeg.
         */
        public function getRawCodecData($read_from_cache=true)
        {
            return $this->getRawFormatData($read_from_cache);
        }
        
        /**
         * Returns the raw data returned from ffmpeg about the available supported filters.
         *
         * @access public
         * @author Oliver Lillie
         * @param boolean $read_from_cache If true and the data exists within a cache then that data is used. If false
         *  then the data is re-read from ffmpeg.
         * @return string Returns the raw buffer data from ffmpeg.
         */
        public function getRawFiltersData($read_from_cache=true)
        {
            return $this->getRawFormatData($read_from_cache);
        }
        
        /**
         * Returns the raw data returned from ffmpeg about the available supported bitstream filters.
         *
         * @access public
         * @author Oliver Lillie
         * @param boolean $read_from_cache If true and the data exists within a cache then that data is used. If false
         *  then the data is re-read from ffmpeg.
         * @return string Returns the raw buffer data from ffmpeg.
         */
        public function getRawBitstreamFiltersData($read_from_cache=true)
        {
            return $this->getRawFormatData($read_from_cache);
        }
        
        /**
         * Returns the raw data returned from ffmpeg about the available supported protocols.
         *
         * @access public
         * @author Oliver Lillie
         * @param boolean $read_from_cache If true and the data exists within a cache then that data is used. If false
         *  then the data is re-read from ffmpeg.
         * @return string Returns the raw buffer data from ffmpeg.
         */
        public function getRawProtocolsData($read_from_cache=true)
        {
            return $this->getRawFormatData($read_from_cache);
        }
    }
