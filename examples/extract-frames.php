<?php

    namespace PHPVideoToolkit;

    include_once './includes/bootstrap.php';
    
    try
    {
        $video = new Video($example_video_path);
        $process = $video->extractFrames(new Timecode(40), new Timecode(50))
                        ->save('./output/big_buck_bunny_frame_%timecode.jpg', null, Media::OVERWRITE_EXISTING);
        
        echo '<h1>Executed Command</h1>';
        Trace::vars($process->getExecutedCommand());
        echo '<hr /><h1>FFmpeg Process Messages</h1>';
        Trace::vars($process->getMessages());
        echo '<hr /><h1>Buffer Output</h1>';
        Trace::vars($process->getBuffer(true));
        echo '<hr /><h1>Resulting Output</h1>';
        // notice because this is mutliple frames an array is returned instead of an object.
        $frames = $process->getOutput();
        $frame_paths = array();
        if(empty($frames) === false)
        {
            foreach ($frames as $frame)
            {
                array_push($frame_paths, $frame->getMediaPath());
            }
        }
        Trace::vars($frame_paths);
        
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
    }
