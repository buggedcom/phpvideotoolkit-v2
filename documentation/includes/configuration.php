<?php
	
	define('PROGRAM_PATH', '/opt/local/bin');
	define('FFMPEG_PROGRAM', 'ffmpeg');
	define('FFPROBE_PROGRAM', 'ffprobe');
	define('TEMP_PATH', '../examples/tmp');

	require_once '../autoloader.php';	
	require_once '../vendor/autoload.php';

//  define the error callback
    function __errorHandler()
    {      
        $args = func_get_args();      
        $count = func_num_args();  
		\PHPVideoToolkit\Trace::vars('ERROR---------', $count === 1 ? 'exception' : 'error', $args);
    }
    set_error_handler('__errorHandler');
    set_exception_handler('__errorHandler');
	
	\PHPVideoToolkit\Factory::setDefaultVars(TEMP_PATH, PROGRAM_PATH, FFMPEG_PROGRAM, FFPROBE_PROGRAM);
	
	require dirname(__FILE__).'/functions.php';
