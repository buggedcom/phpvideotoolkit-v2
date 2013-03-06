<?php

	try
	{
		$config = new \PHPVideoToolkit\Config(array(
			'temp_directory' => './tmp',
			'ffmpeg' => '/opt/local/bin/ffmpeg',
			'ffprobe' => '/opt/local/bin/ffprobe',
			'yamdi' => '/opt/local/bin/yamdi',
			'qt-faststart' => '/opt/local/bin/qt-faststart',
		));
	}
	catch(\PHPVideoToolkit\Exception $e)
	{
	}