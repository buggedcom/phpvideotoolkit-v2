<?php

	include_once './includes/bootstrap.php';
	
	try
	{
 		$video = new \PHPVideoToolkit\Video($example_video_path, $config);
		$output = $video->extractFrames(new \PHPVideoToolkit\Timecode(40), new \PHPVideoToolkit\Timecode(50))
			   			->save('./output/big_buck_bunny_frame_%timecode.jpg');
		
		// $output is an array of \PHPVideoToolkit\Image objects upon success.
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