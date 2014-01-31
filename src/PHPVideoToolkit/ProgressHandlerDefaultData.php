<?php
    
    /**
     * This file is part of the PHP Video Toolkit v2 package.
     *
     * @author Oliver Lillie (aka buggedcom) <publicmail@buggedcom.co.uk>
     * @license Dual licensed under MIT and GPLv2
     * @copyright Copyright (c) 2008-2013 Oliver Lillie <http://www.buggedcom.co.uk>
     * @package PHPVideoToolkit V2
     * @version 2.0.1
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
        protected function _getDefaultData()
        {
            return array(
                'error'      => false,
                'error_message' => null,
                'started'    => false, // true when the process has started
                'finished'   => false, // true when the process has ended by interuption or success completion
                'completed'  => false, // true when the process has ended by success completion
                'interrupted'=> false, // true when the process has ended by interuption, ie finished early.
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
                'output_file'=> null,
                'input_file' => null,
                'process_file' => null,
            );
        }
        
    }
