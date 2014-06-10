<?php

    namespace PHPVideoToolkit;

    include_once './includes/bootstrap.php';
    
    session_start();
    
    try
    {
        // important to not that this doesn't affect the actual process and that still carries on in the background regardless.
        if(isset($_GET['reset']) === true)
        {
            unset($_SESSION['process_id']);
        }
        
        if(isset($_SESSION['process_id']) === true)
        {
            Trace::vars('Process ID found in session...');
            
            $handler = new ProgressHandlerPortable($_SESSION['process_id'], $config);

            Trace::vars('Probing progress handler...');
            
            $probe = $handler->probe();
            Trace::vars($probe);
            if($probe['finished'] === true)
            {
                Trace::vars('Process has completed.');
                unset($_SESSION['process_id']);
                echo '<a href="?reset=1">Restart Process</a>';
                exit;
            }
            
            echo '<meta http-equiv="refresh" content="1; url=?'.time().'">';
            echo '<a href="?reset=1">Reset Process</a>';
        
            exit;
        }
        
        Trace::vars('Starting new encode...');

        $multi_output = new MultiOutput($config);

        $mp3_output = './output/big_buck_bunny.multi1.mp3';
        $format = Format::getFormatFor($mp3_output, $config, 'AudioFormat');
        $multi_output->addOutput($mp3_output, $format);

        $ogg_output = './output/big_buck_bunny.multi2.ogg';
        $format = Format::getFormatFor($ogg_output, $config, 'VideoFormat');
        $format->setVideoDimensions(VideoFormat::DIMENSION_SQCIF);
        $multi_output->addOutput($ogg_output, $format);

        $ogg_output = './output/big_buck_bunny.multi3.ogg';
        $format = Format::getFormatFor($ogg_output, $config, 'VideoFormat');
        $format->setVideoDimensions(VideoFormat::DIMENSION_XGA);
        $multi_output->addOutput($ogg_output, $format);

        $threegp_output = './output/big_buck_bunny.multi4.3gp';
        $format = Format::getFormatFor($threegp_output, $config, 'VideoFormat');
        $format->setVideoDimensions(VideoFormat::DIMENSION_XGA);
        $multi_output->addOutput($threegp_output, $format);

        $threegp_output = './output/big_buck_bunny.multi5.3gp';
        $format = Format::getFormatFor($threegp_output, $config, 'VideoFormat');
        $format->setVideoDimensions(VideoFormat::DIMENSION_SVGA);
        $format->setVideoFrameRate(10);
        $format->videoFlipVertical();
        $multi_output->addOutput($threegp_output, $format);

        $aac_output = './output/big_buck_bunny.multi6.aac';
        $format = Format::getFormatFor($aac_output, $config, 'AudioFormat');
        $multi_output->addOutput($aac_output, $format);
        
        $video = new Video($example_video_path, $config);
        $process = $video->saveNonBlocking($multi_output, null, Video::OVERWRITE_EXISTING);

        $id = $process->getPortableId();
        $_SESSION['process_id'] = $id;
        
        echo '<h1>Process ID</h1>';
        Trace::vars($id);
        
        echo '<h1>Executed Command</h1>';
        Trace::vars($process->getExecutedCommand());
        
        
        echo '<meta http-equiv="refresh" content="1; url=?'.time().'">';
        
        exit;
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
        
        echo '<a href="?reset=1">Reset Process</a>';
    }
    catch(Exception $e)
    {
        echo '<h1>Error</h1>';
        Trace::vars($e->getMessage());
        echo '<h2>Exception</h2>';
        Trace::vars($e);

        echo '<a href="?reset=1">Reset Process</a>';
    }
