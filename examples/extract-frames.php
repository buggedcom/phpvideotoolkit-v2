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
		$output = $video->extractFrames(new \PHPVideoToolkit\Timecode(40), new \PHPVideoToolkit\Timecode(50))
			   			->save('./output/big_buck_bunny_frame_%timecode.jpg');
		
		echo '<hr /><h1>Executed Command</h1>'.($video->getProcess()->getExecutedCommand());
		echo '<hr /><h1>Buffer Output</h1><pre>'.($video->getProcess()->getBuffer()).'</pre>';
	}
	catch(\PHPVideoToolkit\Exception $e)
	{
		if($video->getProcess()->isCompleted())
		{
			\PHPVideoToolkit\Trace::vars($video->getProcess()->getExecutedCommand());
			\PHPVideoToolkit\Trace::vars($video->getProcess()->getBuffer());
		}
		\PHPVideoToolkit\Trace::vars($e);
	}