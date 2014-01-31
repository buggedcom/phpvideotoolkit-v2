<?php

    include_once './includes/bootstrap.php';
    
    try
    {
        $video = new \PHPVideoToolkit\Video($example_video_path, $config);
        $process = $video->getProcess();

        $progress_handler = new \PHPVideoToolkit\ProgressHandlerNative(function($data)
        {
        	// echo '<pre>'.print_r($data, true).'</pre>';
            // do something here...
            // IMPORTANT NOTE: most modern browser don't support output buffering any more.
        }, $config);

        /*
         ...or...

         $progress_handler = new ProgressHandlerNative(null, $config);

         ...then after a call to saveNonBlocking...
         
         while($progress_handler->completed !== true)
         {
             // note setting true in probe() automatically tells the probe to wait after the data is returned.
             echo '<pre>'.print_r($progress_handler->probe(true), true).'</pre>';
         }
         
        */

        $output = $video->purgeMetaData()
                        ->setMetaData('title', 'Hello World')
                        ->save('./output/big_buck_bunny.3gp', null, \PHPVideoToolkit\Video::OVERWRITE_EXISTING, $progress_handler);

        echo '<h1>Executed Command</h1>';
        \PHPVideoToolkit\Trace::vars($process->getExecutedCommand());
        echo '<hr /><h1>FFmpeg Process Messages</h1>';
        \PHPVideoToolkit\Trace::vars($process->getMessages());
        echo '<hr /><h1>Buffer Output</h1>';
        \PHPVideoToolkit\Trace::vars($process->getBuffer(true));
        echo '<hr /><h1>Resulting Output</h1>';
        \PHPVideoToolkit\Trace::vars($output->getOutput()->getMediaPath());
        
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