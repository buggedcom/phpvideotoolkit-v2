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
     * @access public
     * @author Oliver Lillie
     * @package default
     */
    class ProgressHandlerDefaultData
    {
        const ENCODING_STATUS_PENDING = 'pending';
        const ENCODING_STATUS_DECODING = 'decoding';
        const ENCODING_STATUS_ENCODING = 'encoding';
        const ENCODING_STATUS_FINALISING = 'finalising';
        const ENCODING_STATUS_COMPLETED = 'completed';
        const ENCODING_STATUS_FINISHED = 'finished';
        const ENCODING_STATUS_INTERRUPTED = 'interrupted';
        const ENCODING_STATUS_ERROR = 'error';

        protected $_total_duration;

        protected function _getDefaultData()
        {
            return array(
                'error'      => false,
                'error_message' => null,
                'started'    => false, // true when the process has started
                'finished'   => false, // true when the process has ended by interuption or success completion
                'completed'  => false, // true when the process has ended by success completion
                'interrupted'=> false, // true when the process has ended by interuption, ie finished early.
                'status'     => self::ENCODING_STATUS_PENDING,
                'run_time'   => 0,
                'percentage' => 0,
                'fps_avg'    => 0,
                'size'       => 0,
                'frame'      => 0,
                'duration'   => 0,
                'expected_duration' => $this->_total_duration,
                'fps'        => 0,
                'dup'        => 0,
                'drop'       => 0,
                'output_count'=> 0,
                'output_file'=> null,
                'input_count'=> 0,
                'input_file' => null,
                'process_file' => null,
            );
        }
        
    }
