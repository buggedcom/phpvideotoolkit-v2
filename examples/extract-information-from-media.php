<?php

    include_once './includes/bootstrap.php';
    
    foreach (array(
        $example_video_path => '\PHPVideoToolkit\Video',
        $example_audio_path => '\PHPVideoToolkit\Audio',
    ) as $path => $class)
    {
        try
        {
            $phpvideotoolkit_media = new $class($path, $config);
            $output = $phpvideotoolkit_media->read();
        
            echo '<hr /><h1>Resulting Output for '.pathinfo($path, PATHINFO_BASENAME).'</h1>';
            \PHPVideoToolkit\Trace::vars($output);

        }
        catch(\PHPVideoToolkit\FfmpegProcessOutputException $e)
        {
            echo '<h1>Error</h1>';
            \PHPVideoToolkit\Trace::vars($e);

            $process = $phpvideotoolkit_media->getProcess();
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
    }
