<?php

	include_once './includes/bootstrap.php';
	
	try
	{
 		$video = new \PHPVideoToolkit\Video('media/BigBuckBunny_320x180.mp4', $config);
		$process = $video->getProcess();
	//	$process->setProcessTimelimit(1);
		$output = $video->extractSegment(new \PHPVideoToolkit\Timecode(10), new \PHPVideoToolkit\Timecode(20))
						->save('./output/big_buck_bunny_'.time().'.mp3', new \PHPVideoToolkit\AudioFormat_Mp3('output', '/opt/local/bin/ffmpeg', './tmp'));
		
		
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