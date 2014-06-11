<?php

    namespace PHPVideoToolkit;

    include_once './includes/bootstrap.php';
    
    ini_set('memory_limit', '1024M');
    
    function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }   
    
    try
    {
        $convert = $config->convert;
        $transcoders = array(
            'php', 
            'convert', 
            'gifsicle-with-gd',
            'gifsicle-with-convert',
        );
        
        foreach ($transcoders as $gif_transcoder)
        {
            $start = microtime_float();
            
            $output_path = './output/big_buck_bunny.'.$gif_transcoder.'.gif';
            
            if($gif_transcoder === 'gifsicle-with-gd')
            {
                $config->convert = null;
                $config->gif_transcoder = 'gifsicle';
            }
            else if($gif_transcoder === 'gifsicle-with-convert')
            {
                $config->convert = $convert;
                $config->gif_transcoder = 'gifsicle';
            }
            else if($gif_transcoder === 'convert')
            {
                $config->convert = $convert;
                $config->gif_transcoder = 'convert';
            }

            $output_format = Format::getFormatFor($output_path, $config, 'ImageFormat');
            $output_format->setVideoFrameRate(12);
        
            $video = new Video($example_video_path, $config);
            $process = $video->extractSegment(new Timecode(10), new Timecode(30))
                            ->save($output_path, $output_format, Media::OVERWRITE_EXISTING);
            
            $length = microtime_float()-$start;
            
            echo '<h1>'.str_replace('-', ' ', $gif_transcoder).'</h1>';
            echo 'File = '.$output_path.'<br />';
            echo 'Time to encode = '.$length.'<br />';
            echo 'File size = '.(filesize($output_path)/1024/1024).' MB<br />';
        }
        
    }
    catch(FfmpegProcessOutputException $e)
    {
        echo '<h1>Error</h1>';
        Trace::vars($e->getMessage());
        echo '<h2>FfmpegProcessOutputException</h2>';
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
