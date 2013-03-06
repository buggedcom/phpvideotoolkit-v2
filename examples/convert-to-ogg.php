<?php

	include_once './includes/boostrap.php';
	
	try
	{
		\PHPVideoToolkit\Factory::setDefaultVars('./tmp', '/opt/local/bin');

 		$video = \PHPVideoToolkit\Factory::video('media/BigBuckBunny_320x180.mp4');
		$process = $video->getProcess();
	//	$process->setProcessTimelimit(1);
		$output = $video->extractSegment(new \PHPVideoToolkit\Timecode(10), new \PHPVideoToolkit\Timecode(20))
						->save('./output/big_buck_bunny.ogg');
		
		
		echo '<h1>Executed Command</h1>';
		\PHPVideoToolkit\Trace::vars($process->getExecutedCommand());
		echo '<hr /><h1>FFmpeg Process Messages</h1>';
		\PHPVideoToolkit\Trace::vars($process->getMessages());
		echo '<hr /><h1>Buffer Output</h1>';
		\PHPVideoToolkit\Trace::vars($process->getBuffer(true));
		
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
	}
	catch(\PHPVideoToolkit\Exception $e)
	{
		echo '<h1>Error</h1>';
		\PHPVideoToolkit\Trace::vars($e);
	}