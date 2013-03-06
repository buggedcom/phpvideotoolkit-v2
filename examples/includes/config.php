<?php

	try
	{
		$config = new \PHPVideoToolkit\Config(array(
			'temp_directory' => './tmp',
			'ffmpeg' => '/opt/local/bin/ffmpeg',
			'ffprobe' => '/opt/local/bin/ffprobe',
			'yamdi' => '/opt/local/bin/yamdi',
			'qtfaststart' => '/opt/local/bin/qt-faststart',
		));
	}
	catch(\PHPVideoToolkit\Exception $e)
	{
		echo '<h1>Config set errors</h1>';
		\PHPVideoToolkit\Trace::vars($e);
		exit;
	}
