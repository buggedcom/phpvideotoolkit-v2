<?php

    include_once './includes/bootstrap.php';
    
    try
    {
        $video = new \PHPVideoToolkit\Video($example_video_path, $config);
        $process = $video->getProcess();

        $output = $video->extractFrames(new \PHPVideoToolkit\Timecode(40), new \PHPVideoToolkit\Timecode(50))
                        ->save('./output/big_buck_bunny_frame_%timecode.jpg', null, \PHPVideoToolkit\Media::OVERWRITE_EXISTING);
        
        \PHPVideoToolkit\Trace::vars($output);
        

        echo '<h1>Executed Command</h1>';
        \PHPVideoToolkit\Trace::vars($process->getExecutedCommand());
        echo '<hr /><h1>FFmpeg Process Messages</h1>';
        \PHPVideoToolkit\Trace::vars($process->getMessages());
        echo '<hr /><h1>Buffer Output</h1>';
        \PHPVideoToolkit\Trace::vars($process->getBuffer(true));
        echo '<hr /><h1>Resulting Output</h1>';
        // notice because this is mutliple frames an array is returned instead of an object.
        $frames = $output->getOutput();
        $frame_paths = array();
        if(empty($frames) === false)
        {
            foreach ($frames as $frame)
            {
                array_push($frame_paths, $frame->getMediaPath());
            }
        }
        \PHPVideoToolkit\Trace::vars($frame_paths);
        
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
