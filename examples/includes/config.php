<?php

    try
    {
        $config = new \PHPVideoToolkit\Config(array(
            'temp_directory'              => './tmp',
            'ffmpeg'                      => '/opt/local/bin/ffmpeg',
            'ffprobe'                     => '/opt/local/bin/ffprobe',
            'yamdi'                       => '/opt/local/bin/yamdi',
            'qtfaststart'                 => '/opt/local/bin/qt-faststart',
            'gif_transcoder'              => 'php',
            'gif_transcoder_convert_use_dither'    => false,
            'gif_transcoder_convert_use_coalesce'  => false,
            'gif_transcoder_convert_use_map'       => false,
            'convert'                     => '/opt/local/bin/convert',
            'gifsicle'                    => '/opt/local/bin/gifsicle',
            'php_exec_infinite_timelimit' => true,
            'cache_driver'                => 'InTempDirectory',
            'set_default_output_format'   => true,
        ), true);
    }
    catch(\PHPVideoToolkit\Exception $e)
    {
        echo '<h1>Config set errors</h1>';
        \PHPVideoToolkit\Trace::vars($e);
        exit;
    }

    $example_video_path = BASE.'examples/media/BigBuckBunny_320x180.mp4';
    $example_video_path1 = BASE.'examples/media/tc.mov';
    $example_audio_path = BASE.'examples/media/Ballad_of_the_Sneak.mp3';

    $example_images_dir = BASE.'examples/media/images/';
    $example_image_paths = array(
        $example_images_dir.'P1110741.jpg',
        $example_images_dir.'P1110742.jpg',
        $example_images_dir.'P1110743.jpg',
        $example_images_dir.'P1110744.jpg',
        $example_images_dir.'P1110745.jpg',
        $example_images_dir.'P1110746.jpg',
        $example_images_dir.'P1110753.jpg',
        $example_images_dir.'P1110754.jpg',
        $example_images_dir.'P1110755.jpg',
        $example_images_dir.'P1110756.jpg',
    );
    
