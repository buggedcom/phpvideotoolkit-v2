<?php

	include_once './includes/bootstrap.php';
	
	try
	{
 		$video = new \PHPVideoToolkit\Video($example_video_path, $config);
		$result = $video->extractFrame(new \PHPVideoToolkit\Timecode(50))
			  			->save('./output/extract-frame.example1.jpg');
		
		// $result is an \PHPVideoToolkit\Image object on success
	}
	catch(\PHPVideoToolkit\Exception $e)
	{
		\PHPVideoToolkit\Trace::vars($video->getProcess()->getExecutedCommand());
		\PHPVideoToolkit\Trace::vars($video->getProcess()->getBuffer());
		\PHPVideoToolkit\Trace::vars($video->getProcess()->getLastSplit());
		\PHPVideoToolkit\Trace::vars($e);
	}