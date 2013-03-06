<?php

	include_once './includes/boostrap.php';
	
	try
	{
		$output_format = new \PHPVideoToolkit\VideoFormat('output');
		//$output_format->setVideoFormat('ljpeg');
		
 		$video = new \PHPVideoToolkit\Video('media/BigBuckBunny_320x180.mp4', $config);
		
		$video->extractFrame(new \PHPVideoToolkit\Timecode(50));
		
		$result = $video
			->save('./output/test-'.time().'.jpg', $output_format, \PHPVideoToolkit\Video::OVERWRITE_EXISTING, $progress_handler);
		
		\PHPVideoToolkit\Trace::vars($result);
	}
	catch(\PHPVideoToolkit\Exception $e)
	{
		
		\PHPVideoToolkit\Trace::vars($video->getProcess()->getExecutedCommand());
		\PHPVideoToolkit\Trace::vars($video->getProcess()->getBuffer());
		\PHPVideoToolkit\Trace::vars($video->getProcess()->getLastSplit());
		\PHPVideoToolkit\Trace::vars($e);
	}