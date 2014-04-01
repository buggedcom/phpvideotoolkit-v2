<?php

    include_once './includes/bootstrap.php';

    try
    {
        $video = new \PHPVideoToolkit\Video($example_video_path, $config);
        $process = $video->getProcess();
        
        $video->extractSegment(new \PHPVideoToolkit\Timecode(10), new \PHPVideoToolkit\Timecode(20));

        $output = new \PHPVideoToolkit\MultiOutput();

        $format = new \PHPVideoToolkit\VideoFormat();
        $format->setVideoDimensions(\PHPVideoToolkit\VideoFormat::DIMENSION_SQCIF);
        $output->addOutput('./output/big_buck_bunny.mp4', $format);

        $format = new \PHPVideoToolkit\VideoFormat();
        $format->setVideoDimensions(\PHPVideoToolkit\VideoFormat::DIMENSION_QQVGA);
        $output->addOutput('./output/big_buck_bunny.3gp');

        $extracted_output = $video->save($output);
        
        
        echo '<h1>Executed Command</h1>';
        \PHPVideoToolkit\Trace::vars($process->getExecutedCommand());
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
    }
