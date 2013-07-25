<?php
    
    $current_page = basename($_SERVER['PHP_SELF']);
    
    include_once 'configuration.php';
    
?>

<!DOCTYPE html>
<html lang="en">

<head>
        
    <meta charset="utf-8">
    <title>PHPVideoToolkit V2 Documention.</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="./css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
        body{padding-top:60px;padding-bottom:40px;}
        .sidebar-nav{padding:9px 0;}
        @media (max-width:980px){/* Enable use of floated navbar text */
￿            .navbar-text.pull-right{float:none;padding-left:5px;padding-right:5px;}
        }
    </style>
    <link href="./css/bootstrap-responsive.css" rel="stylesheet">

    <!-- pretty printer -->
    <link href="./css/google-code-prettify/prettify.css" type="text/css" rel="stylesheet" />

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="./js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="./ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="./ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="./ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="./ico/apple-touch-icon-57-precomposed.png">
    <link rel="shortcut icon" href="./ico/favicon.png">
        
        
</head>

<body>

    <div class="navbar navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container-fluid">
                <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="brand" href="#">PHPVideoToolkit</a>
                <div class="nav-collapse collapse">
                    <p class="navbar-text pull-right">
                        <a href="https://github.com/buggedcom/phpvideotoolkit-v2">GitHub Repository</a>
                    </p>
                    <ul class="nav">
                        <li<?php echo $current_page === 'index.php' ? ' class="active"' : ''; ?>><a href="./index.php">Home</a></li>
                        <li<?php echo strpos($current_page, 'documentation.') === 0 ? ' class="active"' : ''; ?>><a href="./documentation.what-phpvideotoolkit-is-not.php">Documentation</a></li>
                        <li<?php echo strpos($current_page, 'examples.') === 0 ? ' class="active"' : ''; ?>><a href="./examples.your-ffmpeg-setup.php">Examples</a></li>
                        <li<?php echo $current_page === 'about-and-license.php' ? ' class="active"' : ''; ?>><a href="./about-and-license.php">About &amp; License</a></li>
                        <li<?php echo $current_page === 'change-log.php' ? ' class="active"' : ''; ?>><a href="./change-log.php">Change Log</a></li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span3">
                <div class="well sidebar-nav">
                    <ul class="nav nav-list">
                        <li class="nav-header">Documentation</li>
                        <li<?php echo $current_page === 'documentation.what-phpvideotoolkit-is-not.php' ? ' class="active"' : ''; ?>><a href="./documentation.what-phpvideotoolkit-is-not.php">What PHPVideoToolkit is NOT</a></li>
                        <li<?php echo $current_page === 'documentation.installing-ffmpeg.php' ? ' class="active"' : ''; ?>><a href="./documentation.installing-ffmpeg.php">Installing FFmpeg</a></li>
                        <li<?php echo $current_page === 'documentation.the-basics.php' ? ' class="active"' : ''; ?>><a href="./documentation.the-basics.php">The Basics</a></li>
                        <li<?php echo $current_page === 'documentation.timecodes.php' ? ' class="active"' : ''; ?>><a href="./documentation.timecodes.php">Timecodes</a></li>
                        <li<?php echo $current_page === 'documentation.input-and-output-formats.php' ? ' class="active"' : ''; ?>><a href="./documentation.input-and-output-formats.php">Input and Output Formats</a></li>
                        <li<?php echo $current_page === 'documentation.advanced-options.php' ? ' class="active"' : ''; ?>><a href="./documentation.advanced-options.php">Advanced Options</a></li>
                        <li<?php echo $current_page === 'documentation.phpvideotoolkit-class-reference.php' ? ' class="active"' : ''; ?>><a href="./documentation.phpvideotoolkit-class-reference.php">PHPVideoToolkit Class Reference</a></li>
                        <li class="divider"></li>
                        <li class="nav-header">Examples</li>
                        <li<?php echo $current_page === 'examples.your-ffmpeg-setup.php' ? ' class="active"' : ''; ?>><a href="./examples.your-ffmpeg-setup.php">Your FFmpeg Setup</a></li>
                        <li<?php echo $current_page === 'examples.export-a-single-frame.php' ? ' class="active"' : ''; ?>><a href="./examples.export-a-single-frame.php">Export a Single Frame</a></li>
                        <li<?php echo $current_page === 'examples.export-a-series-of-frames.php' ? ' class="active"' : ''; ?>><a href="./examples.export-a-series-of-frames.php">Export a Series of Frames</a></li>
                        <li<?php echo $current_page === 'examples.export-animated-gif.php' ? ' class="active"' : ''; ?>><a href="./examples.export-animated-gif.php">Export an Animated Gif</a></li>
                        <li<?php echo $current_page === 'examples.extract-a-segment.php' ? ' class="active"' : ''; ?>><a href="./examples.extract-a-segment.php">Extract a Segment</a></li>
                        <li<?php echo $current_page === 'examples.split-or-chunk-media.php' ? ' class="active"' : ''; ?>><a href="./examples.split-or-chunk-media.php">Split/Chunk Media</a></li>
                        <li<?php echo $current_page === 'examples.extract-audio.php' ? ' class="active"' : ''; ?>><a href="./examples.extract-audio.php">Extract Audio</a></li>
                        <li<?php echo $current_page === 'examples.convert-media-formats.php' ? ' class="active"' : ''; ?>><a href="./examples.convert-media-formats.php">Convert Media Formats</a></li>
                        <li<?php echo $current_page === 'examples.add-watermarking.php' ? ' class="active"' : ''; ?>><a href="./examples.add-watermarking.php">Add Watermarking</a></li>
                        <li<?php echo $current_page === 'examples.overlay-audio.php' ? ' class="active"' : ''; ?>><a href="./examples.overlay-audio.php">Overlay Audio</a></li>
                        <li<?php echo $current_page === 'examples.join-videos.php' ? ' class="active"' : ''; ?>><a href="./examples.join-videos.php">Join Videos</a></li>
                        <li<?php echo $current_page === 'examples.transcoding-without-blocking-PHP.php' ? ' class="active"' : ''; ?>><a href="./examples.transcoding-without-blocking-PHP.php">Transcoding without Blocking PHP</a></li>
                        <li<?php echo $current_page === 'examples.transcoding-with-progress-handlers.php' ? ' class="active"' : ''; ?>><a href="./examples.transcoding-with-progress-handlers.php">Transcoding with Progress Handlers</a></li>
                        <li<?php echo $current_page === 'examples.queuing-transcoding.php' ? ' class="active"' : ''; ?>><a href="./examples.queuing-transcoding.php">Queuing Transcoding</a></li>
                    </ul>
                </div><!--/.well -->
            </div><!--/span-->
            
<?php

    if(PROGRAM_PATH === null)
    {
        
?>

            <div class="span9">
                <div class="alert alert-error">
                    <strong>Configuration Not Configured!</strong> 
                    <p>In order for some of the examples in the documentation to work you need set the configuration options in <?php echo dirname(__FILE__); ?>/configuration.php.</p>
                </div>
            </div>

<?php

    }
    
    if(is_file($example_video_path) === false)
    {
        
?>

            <div class="span9">
                <div class="alert alert-error">
                    <strong>BigBuckBunny_320x180.mp4 Does not exist</strong> 
                    <p>In order for the supplied examples to work, you need to download a version of the animated short Big Buck Bunny. <a href="http://download.blender.org/peach/bigbuckbunny_movies/BigBuckBunny_320x180.mp4">You can download the file here.</a> Once downloaded please move or copy the mp4 to the <?php echo HTML(BASE); ?>examples/media directory. This message will then dissapear.</p>
                </div>
            </div>

<?php

    }
    
    
?>
