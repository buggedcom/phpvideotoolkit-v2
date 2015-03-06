<?php

    namespace PHPVideoToolkit;

    include_once './includes/bootstrap.php';
    
    try
    {
        $audio = new Audio($example_audio_path);
        $process = $audio->getProcess();
        $process->addPreInputCommand('-framerate', '1/5');
        $process->addPreInputCommand('-pattern_type', 'glob');
        $process->addPreInputCommand('-i', $example_images_dir.'*.jpg');
        $process->addCommand('-pix_fmt', 'yuv420p');
        $process->addCommand('-shortest', '');

        $output_format = new VideoFormat();
        $output_format->setVideoFrameRate('1/5')
                      ->setVideoDimensions(320, 240)
                      ->setAudioCodec('libfdk_aac')
                      ->setVideoCodec('mpeg4');

    //  $process->setProcessTimelimit(1);
        $process = $audio->save('./output/my_homemade_video.mp4', $output_format, Media::OVERWRITE_EXISTING);
        
        
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

        $process = $audio->getProcess();
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
