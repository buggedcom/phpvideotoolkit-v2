<?php

	include_once './includes/bootstrap.php';
	
	try
	{
 		$video = new \PHPVideoToolkit\Video('media/BigBuckBunny_320x180.mp4', $config);
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