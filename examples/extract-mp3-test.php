<?php

include_once './includes/bootstrap.php';
	
	try
	{
 		$audio = new \PHPVideoToolkit\Audio('media/Ballad_of_the_Sneak.mp3', $config);
		$process = $audio->getProcess();
		$output = $audio->extractSegment(new \PHPVideoToolkit\Timecode(10), new \PHPVideoToolkit\Timecode(20))
						->save('./output/test-'.time().'.mp3');
		
		
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

		$process = $audio->getProcess();
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