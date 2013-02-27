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

		$output_format = \PHPVideoToolkit\Factory::videoFormat('output');
		//$output_format->setVideoFormat('ljpeg');
		
 		$video = \PHPVideoToolkit\Factory::video('media/BigBuckBunny_320x180.mp4');
		
		$video->extractFrames(new \PHPVideoToolkit\Timecode(40.5), new \PHPVideoToolkit\Timecode(50));
		
		\PHPVideoToolkit\Trace::vars($video->read());
		
		$result = $video
			->save('./output/test-'.time().'%timecode.jpg', $output_format, \PHPVideoToolkit\Video::OVERWRITE_EXISTING, $progress_handler);
		
		\PHPVideoToolkit\Trace::vars($result);
	}
	catch(\PHPVideoToolkit\Exception $e)
	{
		// if($video->getProcess()->isCompleted())
// 		{
// 			\PHPVideoToolkit\Trace::vars($video->getProcess()->getExecutedCommand());
// 			\PHPVideoToolkit\Trace::vars($video->getProcess()->getBuffer());
// 		}
		\PHPVideoToolkit\Trace::vars($e);
	}