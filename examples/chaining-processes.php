<?php

    include_once './includes/bootstrap.php';
    
    ob_start();
    
    echo '<p>This is an example to chain processing on from one output to another to another and so on.</p><hr />';
    echo '<p>.mov status: <strong id="status-1">---</strong></p>';
    echo '<p>resize mov status: <strong id="status-2">---</strong></p>';
    echo '<p>jpeg from resized mov status: <strong id="status-3">---</strong></p>';
    
    ob_flush();
    
    try
    {
        $video = new \PHPVideoToolkit\Video($example_video_path, $config);
        
        $progress_handler = new \PHPVideoToolkit\ProgressHandlerNative(null, $config);

        $process = $video->extractSegment(new \PHPVideoToolkit\Timecode(10), new \PHPVideoToolkit\Timecode(20))
                        ->saveNonBlocking('./output/big_buck_bunny.mov', null, \PHPVideoToolkit\Media::OVERWRITE_EXISTING, $progress_handler);

        $dot_count = -1;
        while($progress_handler->completed !== true)
        {
            $dot_count += 1;
            $data = $progress_handler->probe(true);
            if($data['started'] === true)
            {
                echo '<script>document.getElementById("status-1").innerHTML = '.json_encode('Encoding to mov '.$data['percentage'].'% '.str_pad('', $dot_count, '.')).'</script>';
                echo '&nbsp;';
                ob_flush();
                //sleep(1);
                if($dot_count > 10)
                {
                    $dot_count = -1;
                }
            }
            else
            {
                echo '<script>document.getElementById("status-1").innerHTML = '.json_encode('Waiting for encoding to start').'</script>';
                echo '&nbsp;';
                ob_flush();
            }
            
        }

        if($process->hasError() === true)
        {
            echo '<script>document.getElementById("status-1").innerHTML = '.json_encode('mov encoding encountered an error: '.$process->getErrorCode().'.').'</script>';
            ob_flush();
            // an error was encountered, do something with it.
        }
        else
        {
            $mov = $process->getOutput();
            
            echo '<script>document.getElementById("status-1").innerHTML = '.json_encode('mov Encoded OK, output: "'.$mov->getMediaPath().'".').'</script>';
            ob_flush();
            // encoding has completed and no error was detected so 
            // we can get the output from the process.
        }

        $format = $mov->getDefaultFormat('output');
        $format->setVideoDimensions(100, 100);

        $progress_handler = new \PHPVideoToolkit\ProgressHandlerNative(null, $config);
        $process = $mov->saveNonBlocking('./output/big_buck_bunny_resized.mov', $format, \PHPVideoToolkit\Media::OVERWRITE_EXISTING, $progress_handler);
        
        $dot_count = -1;
        while($progress_handler->completed !== true)
        {
            $dot_count += 1;
            $data = $progress_handler->probe(true);
            if($data['started'] === true)
            {
                echo '<script>document.getElementById("status-2").innerHTML = '.json_encode('Encoding to mov '.$data['percentage'].'% '.str_pad('', $dot_count, '.')).'</script>';
                echo '&nbsp;';
                ob_flush();
                //sleep(1);
                if($dot_count > 10)
                {
                    $dot_count = -1;
                }
            }
            else
            {
                echo '<script>document.getElementById("status-2").innerHTML = '.json_encode('Waiting for encoding to start').'</script>';
                echo '&nbsp;';
                ob_flush();
            }
            
        }

        if($process->hasError() === true)
        {
            echo '<script>document.getElementById("status-2").innerHTML = '.json_encode('Encoding encountered an error: '.$process->getErrorCode().'.').'</script>';
            ob_flush();
            // an error was encountered, do something with it.
        }
        else
        {
            $resized_mov = $process->getOutput();
            
            echo '<script>document.getElementById("status-2").innerHTML = '.json_encode('Encoded OK, output: "'.$resized_mov->getMediaPath().'".').'</script>';
            ob_flush();
            // encoding has completed and no error was detected so 
            // we can get the output from the process.
        }
        
        $format = new \PHPVideoToolkit\ImageFormat_Jpeg('output', $config);
        $format->setVideoMaxFrames(1); // prevents ffmpeg throwing a fit if %d is not used.

        $process = $resized_mov->saveNonBlocking('./output/big_buck_bunny_resized.jpg', $format, \PHPVideoToolkit\Media::OVERWRITE_EXISTING);

        if($process->hasError() === true)
        {
            echo '<script>document.getElementById("status-3").innerHTML = '.json_encode('Encoding encountered an error: '.$process->getErrorCode().'.').'</script>';
            ob_flush();
            // an error was encountered, do something with it.
        }
        else
        {
            $jpeg = $process->getOutput();
            
            echo '<script>document.getElementById("status-3").innerHTML = '.json_encode('Encoded OK, output: "'.$jpeg->getMediaPath().'".').'</script>';
            ob_flush();
            // encoding has completed and no error was detected so 
            // we can get the output from the process.
        }
        
    }
    catch(\PHPVideoToolkit\FfmpegProcessOutputException $e)
    {
        echo '<h1>Error</h1>';
        \PHPVideoToolkit\Trace::vars($e);

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
