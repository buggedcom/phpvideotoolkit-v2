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
		 	
		// $data_parser = \PHPVideoToolkit\Factory::ffmpegParser();
		// $data = $data_parser->getFormats();
		// \PHPVideoToolkit\Trace::vars($data);

		$output_format = \PHPVideoToolkit\Factory::videoFormat('output')
			->setStrictness('experimental')
			->setVideoAspectRatio('16:9', false)
		//	->setFormat('mkv');
			->setVideoBitrate('10000k')
			->setVideoFrameRate(30)
			//->setVideoPadding(50, 0, 50, 0)
			->setVideoRotation(true);

 		$video = \PHPVideoToolkit\Factory::video('./output/test-1360859158.mp4');
		$video
			->setMetaData('title', 'Hello')
			->setMetaData('description', 'What the "chuff", this is \' a quote.')
			->extractSegment(
				null,
				new \PHPVideoToolkit\Timecode(2, \PHPVideoToolkit\Timecode::INPUT_FORMAT_SECONDS)
			)
//			->split(5, 0.5)
			->save('./output/test-'.time().'.mp4', $output_format, \PHPVideoToolkit\Video::OVERWRITE_EXISTING);
			
			
			\PHPVideoToolkit\Trace::vars($video->readInformation());
			\PHPVideoToolkit\Trace::vars($video->readRawInformation());
// 				
// 		//\PHPVideoToolkit\Trace::vars($video);
// 		
// 		$video = \PHPVideoToolkit\Factory::video('/Users/ollie/Sites/@Projects/PHPVideoToolkit/v2/git/examples/output/test.mpeg');
// 		$data = $video->getInformation();
// 		\PHPVideoToolkit\Trace::vars($data);
		
	// 
	// $data_parser = new MediaParser('ffmpeg', '/tmp');
	// $data = $data_parser->getInformation('./media/MOV00007.gif');
	// \PHPVideoToolkit\Trace::vars($data);
	// 
	// $data_parser = new MediaParser('ffmpeg', '/tmp');
	// $data = $data_parser->getInformation('./media/MOV00007.3gp');
	// \PHPVideoToolkit\Trace::vars($data);
	// 
	// $data_parser = new MediaParser('ffmpeg', '/tmp');
	// $data = $data_parser->getInformation('./media/Ballad_of_the_Sneak.mp3');
	// \PHPVideoToolkit\Trace::vars($data);
	// 
	// $timecode = new \PHPVideoToolkit\Timecode('00:05:12.21', \PHPVideoToolkit\Timecode::INPUT_FORMAT_TIMECODE, '%hh:%mm:%ss.%ms');
	// \PHPVideoToolkit\Trace::vars($timecode->timecode);
	
	}
	catch(\PHPVideoToolkit\Exception $e)
	{
		\PHPVideoToolkit\Trace::vars($e);
	}