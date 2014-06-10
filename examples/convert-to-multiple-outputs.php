<?php

    namespace PHPVideoToolkit;

    namespace PHPVideoToolkit;

    include_once './includes/bootstrap.php';

    try
    {
        $video = new Video($example_video_path);
        $process = $video->getProcess();
        
        $video->extractSegment(new Timecode(10), new Timecode(20));

        $multi_output = new MultiOutput();

        $ogg_output = './output/big_buck_bunny.multi1.ogg';
        $format = Format::getFormatFor($ogg_output, null, 'VideoFormat');
        $format->setVideoDimensions(VideoFormat::DIMENSION_SQCIF);
        $multi_output->addOutput($ogg_output, $format);

        $threegp_output = './output/big_buck_bunny.multi2.3gp';
        $format = Format::getFormatFor($threegp_output, null, 'VideoFormat');
        $format->setVideoDimensions(VideoFormat::DIMENSION_XGA);
        $multi_output->addOutput($threegp_output, $format);

        $process = $video->save($multi_output, null, Media::OVERWRITE_EXISTING);
        
        
        echo '<h1>Executed Command</h1>';
        Trace::vars($process->getExecutedCommand());
        echo '<h1>Executed Command RAW</h1>';
        Trace::vars($process->getExecutedCommand(true));
        echo '<hr /><h1>FFmpeg Process Messages</h1>';
        Trace::vars($process->getMessages());
        echo '<hr /><h1>Buffer Output</h1>';
        Trace::vars($process->getBuffer(true));
        echo '<hr /><h1>Resulting Output</h1>';
        $output = $process->getOutput();
        $output = array_values($output);
        $paths = array();
        foreach ($output as $obj)
        {
            array_push($paths, $obj->getMediaPath());
        }
        Trace::vars($paths);

    }
    catch(FfmpegProcessOutputException $e)
    {
        echo '<h1>Error</h1>';
        Trace::vars($e);

        $process = $video->getProcess();
        if($process->isCompleted())
        {
            echo '<hr /><h2>Executed Command</h2>';
            Trace::vars($process->getExecutedCommand());
            echo '<h1>Executed Command RAW</h1>';
            Trace::vars($process->getExecutedCommand(true));
            echo '<hr /><h2>FFmpeg Process Messages</h2>';
            Trace::vars($process->getMessages());
            echo '<hr /><h2>Buffer Output</h2>';
            Trace::vars($process->getBuffer(true));
        }
    }
    catch(Exception $e)
    {
        echo '<h1>Error</h1>';
        Trace::vars($e->getMessage());
        echo '<h2>Exception</h2>';
        Trace::vars($e);
        if($process)
        {
            echo '<hr /><h2>Executed Command</h2>';
            Trace::vars($process->getExecutedCommand());
            echo '<h1>Executed Command RAW</h1>';
            Trace::vars($process->getExecutedCommand(true));
        }
    }
