<?php

	try
	{
		$config = new \PHPVideoToolkit\Config(array(
			'temp_directory' => './tmp',
			'ffmpeg' => '/opt/local/bin/ffmpeg',
			'ffprobe' => '/opt/local/bin/ffprobe',
			'yamdi' => '/opt/local/bin/yamdi',
			'qtfaststart' => '/opt/local/bin/qt-faststart',
			'gif_transcoder' => 'php',
			'convert' => '/opt/local/bin/convert',
			'gifsicle' => '/opt/local/bin/gifsicle',
		));
	}
	catch(\PHPVideoToolkit\Exception $e)
	{
		echo '<h1>Config set errors</h1>';
		\PHPVideoToolkit\Trace::vars($e);
		exit;
	}

	$example_video_path = BASE.'examples/media/BigBuckBunny_320x180.mp4';
	$example_audio_path = BASE.'examples/media/Ballad_of_the_Sneak.mp3';
	