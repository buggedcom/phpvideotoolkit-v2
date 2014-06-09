<?php

    include_once './includes/bootstrap.php';

    echo '<a href="?method=blocking">Blocking</a> | <a href="?method=non-blocking">Non blocking</a><br />';
    
    try
    {
        $video = new \PHPVideoToolkit\Video($example_video_path, $config);
        $video->extractSegment(new \PHPVideoToolkit\Timecode(10), new \PHPVideoToolkit\Timecode(70));

        if(isset($_GET['method']) === true && $_GET['method'] === 'blocking')
        {
            echo '<h2>Blocking Method</h2>';

            // If you use a blocking save but want to handle the progress during the block, then assign a callback within
            // the constructor of the progress handler.
            // IMPORTANT NOTE: most modern browser don't support output buffering any more.
            $progress_data = array();
            $progress_handler = new \PHPVideoToolkit\ProgressHandlerNative(function($data) use (&$progress_data)
            {
                // do something here like log to file or db.
                array_push($progress_data, round($data['percentage'], 2).': '.round($data['run_time'], 2));
            }, $config);

            $process = $video->purgeMetaData()
                            ->setMetaData('title', 'Hello World')
                            ->save('./output/big_buck_bunny.3gp', null, \PHPVideoToolkit\Video::OVERWRITE_EXISTING, $progress_handler);
            
            array_unshift($progress_data, 'Percentage Completed: Time taken');
            \PHPVideoToolkit\Trace::vars(implode(PHP_EOL, $progress_data));
        }
        else
        {
            echo '<h2>Non Blocking Method</h2>';

            \PHPVideoToolkit\Trace::vars('This non blocking process can only really be demonstrated easily via php cli.');

            // use a non block save to probe the progress handler after the save has been made.
            // IMPORTANT: this method only works with ->saveNonBlocking as otherwise the progress handler
            // probe will quit after one cycle.
            $progress_handler = new \PHPVideoToolkit\ProgressHandlerNative(null, $config);
            $process = $video->purgeMetaData()
                            ->setMetaData('title', 'Hello World')
                            ->saveNonBlocking('./output/big_buck_bunny.3gp', null, \PHPVideoToolkit\Video::OVERWRITE_EXISTING, $progress_handler);

            while($progress_handler->completed !== true)
            {
                \PHPVideoToolkit\Trace::vars($progress_handler->probe(true, 1));
            }
        }

        echo '<h1>Executed Command</h1>';
        \PHPVideoToolkit\Trace::vars($process->getExecutedCommand());
        echo '<hr /><h1>FFmpeg Process Messages</h1>';
        \PHPVideoToolkit\Trace::vars($process->getMessages());
        echo '<hr /><h1>Buffer Output</h1>';
        \PHPVideoToolkit\Trace::vars($process->getBuffer(true));
        echo '<hr /><h1>Resulting Output</h1>';
        \PHPVideoToolkit\Trace::vars($process->getOutput()->getMediaPath());
        
        exit;
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
        
        echo '<a href="?reset=1">Reset Process</a>';
    }
    catch(\PHPVideoToolkit\Exception $e)
    {
        echo '<h1>Error</h1>';
        \PHPVideoToolkit\Trace::vars($e->getMessage());
        echo '<h2>\PHPVideoToolkit\Exception</h2>';
        \PHPVideoToolkit\Trace::vars($e);

        echo '<a href="?reset=1">Reset Process</a>';
    }
