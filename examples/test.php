<?php

	ini_set('error_reporting', '1');
	ini_set('track_errors', '1');
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	
	require_once '../autoloader.php';
	
	try
	{
		\PHPVideoToolkit\Factory::setDefaultVars('/tmp', '/opt/local/bin');
	
		// $video_format = \PHPVideoToolkit\Factory::videoFormat('input');
		// $video_format->setFormat('flv');
		// \PHPVideoToolkit\Trace::vars($video_format);
		// 	
		// $data_parser = \PHPVideoToolkit\Factory::ffmpegParser();
		// $data = $data_parser->getFormats();
		// \PHPVideoToolkit\Trace::vars($data);
		// exit;
		
		$output_format = \PHPVideoToolkit\Factory::videoFormat('output');
		$output_format->setFormat('flv');
		
		$video = \PHPVideoToolkit\Factory::video('./media/MOV00007.3gp');
		$video->split(array(new \PHPVideoToolkit\Timecode(10.4, \PHPVideoToolkit\Timecode::INPUT_FORMAT_SECONDS)), 0.5);
		$video->save($output_format, './output/test.3gp');
		\PHPVideoToolkit\Trace::vars($video);
		
		exit;
		
	$data_parser = new \PHPVideoToolkit\MediaParser('ffmpeg', '/tmp');
	$data = $data_parser->getInformation('./media/mov02596-2.jpg');
	\PHPVideoToolkit\Trace::vars($data);
	
	$data_parser = new \PHPVideoToolkit\MediaParser('ffmpeg', '/tmp');
	$data = $data_parser->getInformation('./media/MOV00007.gif');
	\PHPVideoToolkit\Trace::vars($data);
	
	$data_parser = new \PHPVideoToolkit\MediaParser('ffmpeg', '/tmp');
	$data = $data_parser->getInformation('./media/MOV00007.3gp');
	\PHPVideoToolkit\Trace::vars($data);
	
	$data_parser = new \PHPVideoToolkit\MediaParser('ffmpeg', '/tmp');
	$data = $data_parser->getInformation('./media/Ballad_of_the_Sneak.mp3');
	\PHPVideoToolkit\Trace::vars($data);
	
	// $timecode = new \PHPVideoToolkit\Timecode('00:05:12.21', \PHPVideoToolkit\Timecode::INPUT_FORMAT_TIMECODE, '%hh:%mm:%ss.%ms');
	// \PHPVideoToolkit\Trace::vars($timecode->timecode);
	
	}
	catch(\PHPVideoToolkit\Exception $e)
	{
		\PHPVideoToolkit\Trace::vars($e);
	}