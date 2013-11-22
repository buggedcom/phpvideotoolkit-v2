<?php

    ini_set('error_reporting', '1');
    ini_set('track_errors', '1');
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    
//  define the error callback
    function __errorHandler()
    {      
        $args = func_get_args();      
        $count = func_num_args(); 
        \PHPVideoToolkit\Trace::vars('ERROR---------', $count === 1 ? 'exception' : 'error', $args);
    }
    set_error_handler('__errorHandler');
    set_exception_handler('__errorHandler');
    
    $basedir = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR;
    define('BASE', $basedir);
 
    if(is_file(BASE.'vendor/autoload.php')) require_once BASE.'vendor/autoload.php';
    
    require_once BASE.'autoloader.php';
    require_once BASE.'examples/includes/config.php';
