<?php

    namespace PHPVideoToolkit;

    include_once './includes/bootstrap.php';

    try
    {
        Trace::vars('Note, this process will purposely NOT work as the additional commands are invalid.');
        
        $video = new Video($example_video_path);

        $process = $video->getProcess();
        $process->addPreInputCommand('-custom-command');
        $process->addCommand('-custom-command-with-arg', 'arg value');
        $process->addPostOutputCommand('-output-command', 'another value');
        
    //  $process->setProcessTimelimit(1);
        $video->save('./output/big_buck_bunny.mp4', null, Media::OVERWRITE_EXISTING);
        
        
        echo '<h1>Raw Executed Command</h1>';
        Trace::vars($process->getExecutedCommand(true));
        echo '<h1>Executed Command</h1>';
        Trace::vars($process->getExecutedCommand());
        echo '<hr /><h1>FFmpeg Process Messages</h1>';
        Trace::vars($process->getMessages());
        echo '<hr /><h1>Buffer Output</h1>';
        Trace::vars($process->getBuffer(true));
        echo '<hr /><h1>Resulting Output</h1>';
        Trace::vars($process->getOutput()->getMediaPath());

    }
    catch(FfmpegProcessOutputException $e)
    {
        echo '<h1>Error</h1>';
        Trace::vars($e);

        $process = $video->getProcess();
        if($process->isCompleted())
        {
            echo '<h1>Raw Executed Command</h1>';
            Trace::vars($process->getExecutedCommand(true));
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
