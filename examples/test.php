<?php

	ini_set('error_reporting', '1');
	ini_set('track_errors', '1');
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	
	require_once '../autoloader.php';
	
	//$data_parser = new \PHPVideoToolkit\DataParserGeneric('/opt/local/bin/ffmpeg', '/tmp');
	//$data = $data_parser->ffmpegInformation();
	// \PHPVideoToolkit\Trace::vars($timecode->timecode);
	
	// $data_parser = new \PHPVideoToolkit\DataParserGeneric('/opt/local/bin/ffmpeg', '/tmp');
	// $data = $data_parser->fileInformation('./media/MOV00007.3gp');
	// \PHPVideoToolkit\Trace::vars($data);
	
	$data_parser = new \PHPVideoToolkit\DataParserGeneric('/opt/local/bin/ffmpeg', '/tmp');
	$data = $data_parser->fileInformation('./media/Ballad_of_the_Sneak.mp3');
	\PHPVideoToolkit\Trace::vars($data);
	
	// $timecode = new \PHPVideoToolkit\Timecode('00:05:12.21', \PHPVideoToolkit\Timecode::INPUT_FORMAT_TIMECODE, '%hh:%mm:%ss.%ms');
	// \PHPVideoToolkit\Trace::vars($timecode->timecode);
	
