<?php

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
            \PHPVideoToolkit\Trace::vars('Process ID found in session...');
            
            $handler = new \PHPVideoToolkit\ProgressHandlerPortable($_SESSION['process_id'], $config);

            \PHPVideoToolkit\Trace::vars('Probing progress handler...');
            
            $probe = $handler->probe();
            \PHPVideoToolkit\Trace::vars($probe);
            if($probe['finished'] === true)
            {
                \PHPVideoToolkit\Trace::vars('Process has completed.');
                unset($_SESSION['process_id']);
                echo '<a href="?reset=1">Restart Process</a>';
                exit;
            }
            
            echo '<meta http-equiv="refresh" content="1; url=?'.time().'">';
            echo '<a href="?reset=1">Reset Process</a>';
        
            exit;
        }
        
        \PHPVideoToolkit\Trace::vars('Starting new encode...');
        
        $video = new \PHPVideoToolkit\Video($example_video_path, $config);
        $process = $video->saveNonBlocking('./output/big_buck_bunny.mp4', null, \PHPVideoToolkit\Video::OVERWRITE_EXISTING);

        $id = $process->getPortableId();
        $_SESSION['process_id'] = $id;
        
        echo '<h1>Process ID</h1>';
        \PHPVideoToolkit\Trace::vars($id);
        
        echo '<h1>Executed Command</h1>';
        \PHPVideoToolkit\Trace::vars($process->getExecutedCommand());
        
        
        echo '<meta http-equiv="refresh" content="1; url=?'.time().'">';
        
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