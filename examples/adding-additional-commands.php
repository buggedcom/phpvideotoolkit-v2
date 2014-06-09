<?php

    include_once './includes/bootstrap.php';

    try
    {
        \PHPVideoToolkit\Trace::vars('Note, this process will not work as the commands are invalid.');
        
        $video = new \PHPVideoToolkit\Video($example_video_path, $config);

        $process = $video->getProcess();
        $process->addPreInputCommand('-custom-command');
        $process->addCommand('-custom-command-with-arg', 'arg value');
        $process->addPostOutputCommand('-output-command', 'another value');
        
    //  $process->setProcessTimelimit(1);
        $video->save('./output/big_buck_bunny.mp4', null, \PHPVideoToolkit\Media::OVERWRITE_EXISTING);
        
        
        echo '<h1>Raw Executed Command</h1>';
        \PHPVideoToolkit\Trace::vars($process->getExecutedCommand(true));
        echo '<h1>Executed Command</h1>';
        \PHPVideoToolkit\Trace::vars($process->getExecutedCommand());
        echo '<hr /><h1>FFmpeg Process Messages</h1>';
        \PHPVideoToolkit\Trace::vars($process->getMessages());
        echo '<hr /><h1>Buffer Output</h1>';
        \PHPVideoToolkit\Trace::vars($process->getBuffer(true));
        echo '<hr /><h1>Resulting Output</h1>';
        \PHPVideoToolkit\Trace::vars($process->getOutput()->getMediaPath());

    }
    catch(\PHPVideoToolkit\FfmpegProcessOutputException $e)
    {
        echo '<h1>Error</h1>';
        \PHPVideoToolkit\Trace::vars($e);

        $process = $video->getProcess();
        if($process->isCompleted())
        {
            echo '<h1>Raw Executed Command</h1>';
            \PHPVideoToolkit\Trace::vars($process->getExecutedCommand(true));
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
