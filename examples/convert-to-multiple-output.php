<?php

    include_once './includes/bootstrap.php';

    try
    {
        $video = new \PHPVideoToolkit\Video($example_video_path, $config);
        $process = $video->getProcess();
        
        $video->extractSegment(new \PHPVideoToolkit\Timecode(10), new \PHPVideoToolkit\Timecode(20));

        $multi_output = new \PHPVideoToolkit\MultiOutput($config);

        $flv_output = './output/big_buck_bunny.multi1.ogg';
        $format = \PHPVideoToolkit\Format::getFormatFor($flv_output, $config, 'VideoFormat');
        $format->setVideoDimensions(\PHPVideoToolkit\VideoFormat::DIMENSION_SQCIF);
        $multi_output->addOutput($flv_output, $format);

        $threegp_output = './output/big_buck_bunny.multi2.3gp';
        $format = \PHPVideoToolkit\Format::getFormatFor($threegp_output, $config, 'VideoFormat');
        $format->setVideoDimensions(\PHPVideoToolkit\VideoFormat::DIMENSION_XGA);
        $multi_output->addOutput($threegp_output, $format);

        $extracted_output = $video->save($multi_output, null, \PHPVideoToolkit\Media::OVERWRITE_EXISTING);
        
        
        echo '<h1>Executed Command</h1>';
        \PHPVideoToolkit\Trace::vars($process->getExecutedCommand());
        echo '<h1>Executed Command RAW</h1>';
        \PHPVideoToolkit\Trace::vars($process->getExecutedCommand(true));
        echo '<hr /><h1>FFmpeg Process Messages</h1>';
        \PHPVideoToolkit\Trace::vars($process->getMessages());
        echo '<hr /><h1>Buffer Output</h1>';
        \PHPVideoToolkit\Trace::vars($process->getBuffer(true));
        echo '<hr /><h1>Resulting Output</h1>';
        \PHPVideoToolkit\Trace::vars($extracted_output->getOutput()->getMediaPath());

    }
    catch(\PHPVideoToolkit\FfmpegProcessOutputException $e)
    {
        echo '<h1>Error</h1>';
        \PHPVideoToolkit\Trace::vars($e);

        $process = $video->getProcess();
        if($process->isCompleted())
        {
            echo '<hr /><h2>Executed Command</h2>';
            \PHPVideoToolkit\Trace::vars($process->getExecutedCommand());
            echo '<h1>Executed Command RAW</h1>';
            \PHPVideoToolkit\Trace::vars($process->getExecutedCommand(true));
            echo '<hr /><h2>FFmpeg Process Messages</h2>';
            \PHPVideoToolkit\Trace::vars($process->getMessages());
            echo '<hr /><h2>Buffer Output</h2>';
            \PHPVideoToolkit\Trace::vars($process->getBuffer(true));
        }
    }
    catch(\PHPVideoToolkit\Exception $e)
    {
        echo '<h1>Error</h1>';
        \PHPVideoToolkit\Trace::vars($e->getMessage());
        echo '<h2>\PHPVideoToolkit\Exception</h2>';
        \PHPVideoToolkit\Trace::vars($e);
        if($process)
        {
            echo '<hr /><h2>Executed Command</h2>';
            \PHPVideoToolkit\Trace::vars($process->getExecutedCommand());
            echo '<h1>Executed Command RAW</h1>';
            \PHPVideoToolkit\Trace::vars($process->getExecutedCommand(true));
        }
    }
