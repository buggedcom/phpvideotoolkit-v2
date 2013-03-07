<?php

	include_once './includes/bootstrap.php';
	
	ini_set('memory_limit', '1024M');
	
	function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}	
	
	try
	{
		$convert = $config->convert;
		$transcoders = array(
			'php', 
			'convert', 
			'gifsicle-with-gd',
			'gifsicle-with-convert',
		);
		
		foreach ($transcoders as $gif_transcoder)
		{
			$start = microtime_float();
			
			$output_path = './output/big_buck_bunny.'.$gif_transcoder.'.gif';
			
			if($gif_transcoder === 'gifsicle-with-gd')
			{
				$config->convert = null;
				$config->gif_transcoder = 'gifsicle';
			}
			else if($gif_transcoder === 'gifsicle-with-convert')
			{
				$config->convert = $convert;
				$config->gif_transcoder = 'gifsicle';
			}
		
			$output_format = \PHPVideoToolkit\Format::getFormatFor($output_path, $config, 'ImageFormat');
			$output_format->setVideoFrameRate(12);
		
	 		$video = new \PHPVideoToolkit\Video('media/BigBuckBunny_320x180.mp4', $config);
			$output = $video->extractSegment(new \PHPVideoToolkit\Timecode(10), new \PHPVideoToolkit\Timecode(70))
							->save($output_path, $output_format);
			
			$length = microtime_float()-$start;
			
			echo '<h1>'.str_replace('-', ' ', $gif_transcoder).'</h1>';
			echo 'File = '.$output_path.'<br />';
			echo 'Time to encode = '.$length.'<br />';
			echo 'File size = '.(filesize($output_path)/1024/1024).' MB<br />';
		}
		
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