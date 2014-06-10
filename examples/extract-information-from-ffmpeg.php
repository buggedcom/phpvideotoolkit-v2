<?php

    namespace PHPVideoToolkit;

    include_once './includes/bootstrap.php';
    
    $ffmpeg = new FfmpegParser();

    $is_available = $ffmpeg->isAvailable();
    Trace::vars('$ffmpeg->isAvailable()', $is_available);

    $ffmpeg_version = $ffmpeg->getVersion();
    Trace::vars('$ffmpeg->getVersion()', $ffmpeg_version);
    
    $has_ffmpeg_php_support = $ffmpeg->hasFfmpegPhpSupport();
    Trace::vars('$ffmpeg->hasFfmpegPhpSupport()', $has_ffmpeg_php_support);
    
    $basic_ffmpeg_information = $ffmpeg->getFfmpegData();
    Trace::vars('$ffmpeg->getFfmpegData()', $basic_ffmpeg_information);
    
    $basic_ffmpeg_information = $ffmpeg->getCommands();
    Trace::vars('$ffmpeg->getCommands()', $basic_ffmpeg_information);
    
    $ffmpeg_formats = $ffmpeg->getFormats();
    Trace::vars('$ffmpeg->getFormats()', $ffmpeg_formats);
    
    $ffmpeg_audio_codecs = $ffmpeg->getCodecs('audio');
    Trace::vars('$ffmpeg->getCodecs(\'audio\')', $ffmpeg_audio_codecs);
    
    $ffmpeg_video_codecs = $ffmpeg->getCodecs('video');
    Trace::vars('$ffmpeg->getCodecs(\'video\')', $ffmpeg_video_codecs);
    
    $ffmpeg_subtitle_codecs = $ffmpeg->getCodecs('subtitle');
    Trace::vars('$ffmpeg->getCodecs(\'subtitle\')', $ffmpeg_subtitle_codecs);
    
    $ffmpeg_bitstream_filters = $ffmpeg->getBitstreamFilters();
    Trace::vars('$ffmpeg->getBitstreamFilters()', $ffmpeg_bitstream_filters);
    
    $ffmpeg_filters = $ffmpeg->getFilters();
    Trace::vars('$ffmpeg->getFilters()', $ffmpeg_filters);
    
    $ffmpeg_protocols = $ffmpeg->getProtocols();
    Trace::vars('$ffmpeg->getProtocols()', $ffmpeg_protocols);
    
    $ffmpeg_pixel_formats = $ffmpeg->getPixelFormats();
    Trace::vars('$ffmpeg->getPixelFormats()', $ffmpeg_pixel_formats);
    

