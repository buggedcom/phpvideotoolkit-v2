<?php

	ini_set('error_reporting', '1');
	ini_set('track_errors', '1');
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	
//  define the error callback
    function __errorHandler()
    {      
        $args = func_get_args();      
        $count = func_num_args();  
		\PHPVideoToolkit\Trace::vars('ERROR---------', $count === 1 ? 'exception' : 'error', $args);
    }
    set_error_handler('__errorHandler');
    set_exception_handler('__errorHandler');
	
	
	require_once '../vendor/autoload.php';
	require_once '../autoloader.php';
	
//	require '../vendor/autoload.php';
	// $stash = new Stash\Pool();
	// print_r($stash);
	// exit;
	
	try
	{
		\PHPVideoToolkit\Factory::setDefaultVars('./tmp', '/opt/local/bin');

 		$video = \PHPVideoToolkit\Factory::video('media/BigBuckBunny_320x180.mp4');
		$output = $video->extractSegment(new \PHPVideoToolkit\Timecode(10), new \PHPVideoToolkit\Timecode(20))
						->save('./output/big_buck_bunny.flv');
		
		$process = $video->getProcess();
		
		echo '<h1>Executed Command</h1>';
		\PHPVideoToolkit\Trace::vars($process->getExecutedCommand());
		echo '<hr /><h1>FFmpeg Process Messages</h1>';
		\PHPVideoToolkit\Trace::vars($process->getMessages());
		echo '<hr /><h1>Buffer Output</h1>';
		\PHPVideoToolkit\Trace::vars($process->getBuffer());
		
	}
	catch(\PHPVideoToolkit\FfmpegProcessOutputException $e)
	{
		$process = $video->getProcess();
		
		if($process->isCompleted())
		{
			echo '<h1>Executed Command</h1>';
			\PHPVideoToolkit\Trace::vars($process->getExecutedCommand());
			echo '<hr /><h1>FFmpeg Process Messages</h1>';
			\PHPVideoToolkit\Trace::vars($process->getMessages());
			echo '<hr /><h1>Buffer Output</h1>';
			\PHPVideoToolkit\Trace::vars($process->getBuffer());
		}
		\PHPVideoToolkit\Trace::vars($e);
	}
	catch(\PHPVideoToolkit\Exception $e)
	{
		\PHPVideoToolkit\Trace::vars($e);
	}