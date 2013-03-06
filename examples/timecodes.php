<?php

	include_once './includes/bootstrap.php';
	
	try
	{
		echo '<h1>Timecodes</h1>';
		echo '<hr />';
		echo '<h2>Setting Timecode value via constructor</h2>';
		$timecode = new \PHPVideoToolkit\Timecode(102.34);
		echo $timecode.'<br />';
		$timecode = new \PHPVideoToolkit\Timecode(102.34, \PHPVideoToolkit\Timecode::INPUT_FORMAT_SECONDS);
		echo $timecode.'<br />';
		$timecode = new \PHPVideoToolkit\Timecode(1.705666667, \PHPVideoToolkit\Timecode::INPUT_FORMAT_MINUTES);
		echo $timecode.'<br />';
		$timecode = new \PHPVideoToolkit\Timecode(.028427778, \PHPVideoToolkit\Timecode::INPUT_FORMAT_HOURS);
		echo $timecode.'<br />';
		$timecode = new \PHPVideoToolkit\Timecode('00:01:42.34', \PHPVideoToolkit\Timecode::INPUT_FORMAT_TIMECODE);
		echo $timecode.'<br />';
		
		echo '<hr />';
		echo '<h2>Adjusting timecode values</h2>';
		
		$timecode = new \PHPVideoToolkit\Timecode('00:01:42.34', \PHPVideoToolkit\Timecode::INPUT_FORMAT_TIMECODE);
		echo $timecode.'<br />';
		$timecode->hours += 15;
		echo $timecode.'<br />';
		$timecode->seconds -= 54125.5;
		echo $timecode.'<br />';
		$timecode->milliseconds -= 18840;
		echo $timecode.'<br />';

		echo '<hr />';
		echo '<h2>Setting a timecode value</h2>';

		$timecode->setSeconds(193.7);
		echo $timecode.'<br />';

		$timecode->setTimecode('12:45:39.01');
		echo $timecode->total_seconds.'<br />';
		echo $timecode->seconds.'<br />';
		
	}
	catch(\PHPVideoToolkit\Exception $e)
	{
		echo '<h1>Error</h1>';
		\PHPVideoToolkit\Trace::vars($e);
	}