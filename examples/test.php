<?php

	ini_set('error_reporting', '1');
	ini_set('track_errors', '1');
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	
	require_once '../vendor/autoload.php';
	require_once '../autoloader.php';
	
//	require '../vendor/autoload.php';
	// $stash = new Stash\Pool();
	// print_r($stash);
	// exit;
	
	try
	{
// 		//$exec = new \PHPVideoToolkit\ExecBuffer("psads", './tmp');
// 		$exec = new \PHPVideoToolkit\ExecBuffer("/opt/local/bin/ffmpeg -i '/Users/ollie/Sites/@Projects/PHPVideoToolkit/v2/git/examples/media/BigBuckBunny_320x180.mp4' -t 240 -y -strict 'experimental' -s '142x80' -aspect '16:9' -b:v '10000k' '/Users/ollie/Sites/@Projects/PHPVideoToolkit/v2/git/examples/output/test-1361797899.mp4'", './tmp');
// 		$exec->setBlocking(false)
// 			 ->setBufferOutput(\PHPVideoToolkit\ExecBuffer::TEMP)
// 			 ->execute(
// 				// function($exec, $buffer, $status)
// // 				{
// // 					if($exec->isCompleted())
// // 					{
// // 						\PHPVideoToolkit\Trace::vars($exec->getExecutedCommand());
// // 						\PHPVideoToolkit\Trace::vars($exec->hasError(), $exec->getErrorCode(), $exec->getPid());
// // 						\PHPVideoToolkit\Trace::vars($exec->getRawBuffer());
// // 						\PHPVideoToolkit\Trace::vars($exec->getBuffer());
// // 					}
// // 					else
// // 					{
// // 						//\PHPVideoToolkit\Trace::vars($exec->getLastSplit());
// // 					}
// // 				}
// 			);
// 		
// 		\PHPVideoToolkit\Trace::vars($exec->getExecutedCommand());
// 
// 		while($exec->isCompleted() === false)
// 		{
// 			\PHPVideoToolkit\Trace::vars('non blocking', $exec->getRunTime(), $exec->getLastLine());	
// 			$exec->wait();		
// 		}	 
// 		
// 		\PHPVideoToolkit\Trace::vars($exec->hasError(), $exec->getErrorCode());
// 		\PHPVideoToolkit\Trace::vars($exec->getRawBuffer());
// 		\PHPVideoToolkit\Trace::vars($exec->getBuffer());
// 		
// 		exit;
		// $process = new \PHPVideoToolkit\FfmpegProcessProgressable('/opt/local/bin/ffmpeg', './tmp');
		// $process->setInput('/Users/ollie/Sites/@Projects/PHPVideoToolkit/v2/git/examples/media/BigBuckBunny_320x180.mp4')
		// 		->addCommand('-y')
		// 		->addCommand('-t', 20)
		// 		->addCommand('-strict', 'experimental')
		// 		->addCommand('-s', '142x80')
		// 		->addCommand('-aspect', '16:9')
		// 		->addCommand('-b:v', '10000k')
		// 		->addCommand('-test')
		// 		->setOutput('/Users/ollie/Sites/@Projects/PHPVideoToolkit/v2/git/examples/output/test-1361797899.mp4')
		// 		->execute(
		// 			function($exec, $buffer, $completion_status)
		// 			{
		// 				\PHPVideoToolkit\Trace::vars($exec->isCompleted(), $buffer, $completion_status);
		// 			}
		// 		);
		
		// exit;
		
//		/opt/local/bin/ffmpeg -i '/Users/ollie/Sites/@Projects/PHPVideoToolkit/v2/git/examples/media/BigBuckBunny_320x180.mp4' -test -y -t '20' -strict 'experimental' -s '142x80' -aspect '16:9' -b:v '10000k' -progress '/Users/ollie/Sites/@Projects/PHPVideoToolkit/v2/git/examples/tmp/_temp_1361797899_bc0e015a20bac15d23776d55e0b0908b_512b630b11695_512b630b116a9.txt' '/Users/ollie/Sites/@Projects/PHPVideoToolkit/v2/git/examples/output/test-1361797899.mp4' > /dev/null 2>&1 &
		//exit;
		
		\PHPVideoToolkit\Factory::setDefaultVars('./tmp', '/opt/local/bin');

		$output_format = \PHPVideoToolkit\Factory::videoFormat('output')
			->setStrictness('experimental')
			->setVideoAspectRatio('16:9', false)
			->setVideoDimensions(100, 80)
		//	->setFormat('mkv');
			->setVideoBitrate('10000k')
			//->setVideoFrameRate(30)
			//->setVideoPadding(50, 0, 50, 0)
			->setVideoRotation(true);
		
		// ob_start();
		// $progress_handler = new \PHPVideoToolkit\ProgressHandlerOutput(function($data)
		// {
		// 	\PHPVideoToolkit\Trace::vars($data);
		// 	ob_flush();
		// });
		
		$progress_handler = new \PHPVideoToolkit\ProgressHandlerNative(function($data, Media $output=null)
		{
			\PHPVideoToolkit\Trace::vars($data);
		});

 		$video = \PHPVideoToolkit\Factory::video('media/BigBuckBunny_320x180.mp4');
		
		//$video->getExecProcess()->addCommand('-test');
		
		$video
			//->purgeMetaData()
			//->setMetaData('title', 'Hello')
			//->setMetaData('description', 'What the "chuff", this is \' a quote.')
			 ->extractSegment(
			  				null,
			  				new \PHPVideoToolkit\Timecode(20, \PHPVideoToolkit\Timecode::INPUT_FORMAT_SECONDS)
			  			)
			//->split(60, 0.5)
			->save('./output/test-'.time().'.mp4', $output_format, \PHPVideoToolkit\Video::OVERWRITE_EXISTING, $progress_handler);
		
		\PHPVideoToolkit\Trace::vars($video);exit;
			
		// while($progress_handler->completed !== true)
		// {
		// 	$data = $progress_handler->probe(true);
		// 	\PHPVideoToolkit\Trace::vars($data);
		// 	$progress_handler->wait();
		// }
			
			//\PHPVideoToolkit\Trace::vars($video->readInformation());
			//\PHPVideoToolkit\Trace::vars($video->readRawInformation());
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