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
	 
    class Trace
    {
           
        protected static $_trace_order = 0;
        
        const RETURN_OUTPUT = '-0.293001299';

        public static function where($output=true)
        {   
            $path = dirname(dirname(dirname(__FILE__)));
            $stack = debug_backtrace();
            array_shift($stack);
            $deets = array_shift($stack);
            if($output === true)
            {             
                echo '<pre><span style="font-size:10px;">Debugging <font color=red>'.str_replace($path, '', $deets['file']).'</font> on line <font color=red>'.$deets['line'].'</font></span></pre>';
            }
            return $deets;
        }

        public static function vars()
        {   
            self::$_trace_order += 1;              
            $arg_count = func_num_args();
            $args = func_get_args(); 
            $var = $arg_count === 1 ? $args[0] : $args;
            
            $output = '';  
            
            $path = dirname(dirname(dirname(__FILE__)));
            $trace = self::where(false);
            if($arg_count === 0)
            { 
                $output .= '<pre><span style="font-size:10px;">('.self::$_trace_order.') Called from <font color="red" title="'.$trace['file'].'">'.str_replace($path, '', $trace['file']).'</font> on line <font color="red">'.$trace['line'].'</font> <span style="color:#555">@ '.date('d/m/Y H:i:s').'</span></span></pre>';
                echo $output;
                return;
            }
            
            if(is_array($var) === true && isset($var[0]) === true && $var[0] === self::RETURN_OUTPUT)
            {
                array_shift($var);
            }
            
            $output .= '<pre style="text-transform:none;"><span id="'.self::$_trace_order.'-trace-hide" onclick="this.style.display=\'none\';document.getElementById(\''.self::$_trace_order.'-trace-box\').style.display=\'none\';document.getElementById(\''.self::$_trace_order.'-trace-show\').style.display=\'inline\';" style="color:#ccc;font-size:10px;">[hide]</span><span id="'.self::$_trace_order.'-trace-show" style="display:none;cusor:pointer;color:#ccc;font-size:10px;" onclick="this.style.display=\'none\';document.getElementById(\''.self::$_trace_order.'-trace-box\').style.display=\'block\';document.getElementById(\''.self::$_trace_order.'-trace-hide\').style.display=\'inline\';">[show]</span><span style="font-size:10px;"> ('.self::$_trace_order.') Debugging <font color="red" title="'.(isset($trace['file']) === true ? str_replace($path, '', $trace['file']) : 'eval').'">'.(isset($trace['file']) === true ? str_replace($path, '', $trace['file']) : 'eval').'</font> on line <font color="red">'.(isset($trace['line']) === true ? $trace['line'] : 'unknown').'</font> <span style="color:#555">@ '.date('d/m/Y H:i:s').'</span></span>: 
<div id="'.self::$_trace_order.'-trace-box" style="background-color:#F0F0F0;color:#000;padding:5px;max-width:1000px;max-height:500px;overflow:auto;text-transform:none;">';

            if (is_string($var) === true)
            {
                $output .= htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
            }
            else if (is_numeric($var) === true || is_float($var) === true || is_int($var) === true)
            {
                $output .= $var == 0 ? '0' : $var;
            }
            else if (is_bool($var) === true)
            {
                $output .= $var === false ? 'false' : 'true';
            }
            else if (is_null($var) === true)
            {
                $output .= 'NULL';
            }
            else
            {
                $print_r = print_r($var, true);
                if ((strstr($print_r, '<') !== false) || (strstr($print_r, '>') !== false))
                {
                    $print_r = htmlspecialchars($print_r, ENT_QUOTES, 'UTF-8');
                }
                $output .= $print_r;
            }
            $output .= '</div></pre>';  
            
            if($args[0] !== self::RETURN_OUTPUT)
            {
                echo $output;
                return;
            }
            return $output;
        }
    }
