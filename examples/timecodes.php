<?php

    namespace PHPVideoToolkit;

    include_once './includes/bootstrap.php';
    
    try
    {
        echo '<h1>Timecodes</h1>';
        echo '<hr />';
        echo '<h2>Setting Timecode value via constructor</h2>';
        $timecode = new Timecode(102.34);
        echo 'new Timecode(102.34); = '.$timecode.'<br />';
        $timecode = new Timecode(102.34, Timecode::INPUT_FORMAT_SECONDS);
        echo 'new Timecode(102.34, Timecode::INPUT_FORMAT_SECONDS); = '.$timecode.'<br />';
        $timecode = new Timecode(1.705666667, Timecode::INPUT_FORMAT_MINUTES);
        echo 'new Timecode(1.705666667, Timecode::INPUT_FORMAT_MINUTES); = '.$timecode.'<br />';
        $timecode = new Timecode(.028427778, Timecode::INPUT_FORMAT_HOURS);
        echo 'new Timecode(.028427778, Timecode::INPUT_FORMAT_HOURS); = '.$timecode.'<br />';
        $timecode = new Timecode('00:01:42.34', Timecode::INPUT_FORMAT_TIMECODE);
        echo 'new Timecode(\'00:01:42.34\', Timecode::INPUT_FORMAT_TIMECODE); = '.$timecode.'<br />';
        $timecode = new Timecode(60);
        echo 'new Timecode(60); = '.$timecode.'<br />';
        $timecode = new Timecode(360);
        echo 'new Timecode(360); = '.$timecode.'<br />';
        
        echo '<hr />';
        echo '<h2>Adjusting timecode values</h2>';
        
        $timecode = new Timecode('00:01:42.34', Timecode::INPUT_FORMAT_TIMECODE, 24);
        echo '$timecode = new Timecode(\'00:01:42.34\', Timecode::INPUT_FORMAT_TIMECODE); = '.$timecode.'<br />';
        $adjustments = array(
            array(15, 'hours', true),
            array(-54102.34, 'seconds', true),
            array(-99, 'milliseconds', true),
            array(59, 'seconds', true),
            array(1, 'seconds', false),
            array(59, 'seconds', true),
            array(999, 'milliseconds', true),
            array(1, 'milliseconds', true),
            array(48, 'frames', false),
            array(-15, 'frames', true),
            array(-1, 'seconds', true),
            array(-375, 'milliseconds', true),
        );
        foreach ($adjustments as $value)
        {
            if($value[2] === true)
            {
                $timecode->{$value[1]} += $value[0];
                echo '$timecode->'.$value[1].' += '.$value[0].'; // = '.$timecode->getTimecode('%hh:%mm:%ss:%ms').'<br />';
            }
            else
            {
                echo '<Br />$timecode->reset();<br />';
                $timecode->reset();
                $timecode->{$value[1]} = $value[0];
                echo '$timecode->'.$value[1].' = '.$value[0].'; // = '.$timecode->getTimecode('%hh:%mm:%ss:%ms').'<br />';
            }
        }
        
        echo '<hr />';
        echo '<h2>Setting a timecode value</h2>';

        $timecode->setSeconds(193.7);
        echo '$timecode->setSeconds(193.7); = '.$timecode.'<br />';

        $timecode->setTimecode('12:45:39.01');
        echo '<br /><strong>IMPORTANT: Notice the difference between total_seconds and seconds</strong><br />$timecode->setTimecode(\'12:45:39.01\'); <br />';
        echo '$timecode->total_seconds = '.$timecode->total_seconds.'<br />';
        echo '$timecode->seconds = '.$timecode->seconds.'<br />';
        
    }
    catch(Exception $e)
    {
        echo '<h1>Error</h1>';
        Trace::vars($e->getMessage());
        echo '<h2>Exception</h2>';
        Trace::vars($e);
    }
