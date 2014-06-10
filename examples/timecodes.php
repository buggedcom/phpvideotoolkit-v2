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
        
        echo '<hr />';
        echo '<h2>Adjusting timecode values</h2>';
        
        $timecode = new Timecode('00:01:42.34', Timecode::INPUT_FORMAT_TIMECODE);
        echo 'new Timecode(\'00:01:42.34\', Timecode::INPUT_FORMAT_TIMECODE); = '.$timecode.'<br />';
        $timecode->hours += 15;
        echo '$timecode->hours += 15; = '.$timecode.'<br />';
        $timecode->seconds -= 54125.5;
        echo '$timecode->seconds -= 54125.5; = '.$timecode.'<br />';
        $timecode->milliseconds -= 18840;
        echo '$timecode->milliseconds -= 18840; = '.$timecode.'<br />';

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
