#PHPVideoToolkit V2...

...is a set of PHP classes aimed to provide a modular, object oriented and accessible interface for interacting with videos and audio through FFmpeg.

It also currently provides FFmpeg-PHP emulation in pure PHP so you wouldn't need to compile and install the module. As FFmpeg-PHP has not been updated since 2007 using FFmpeg-PHP with a new version of FFmpeg can often break the module. Using PHPVideoToolkits' emulation of FFmpeg-PHP's functionality allows you to upgrade FFmpeg without worrying about breaking existing funcitonality.

**IMPORTANT** PHPVideoToolkit has only been tested with v1.1.2 of FFmpeg. Whilst the majority of functionality should work regardless of your version of FFmpeg I cannot guarantee it. If you find a bug or have a patch please open a ticket or submit a pull request on https://github.com/buggedcom/phpvideotoolkit-v2

##License

PHPVideoToolkit Copyright (c) 2012-2013 Oliver Lillie

DUAL Licensed under MIT and GPL v2

See LICENSE.md for more details.

##Documentation

Extensive documentation and examples are bundled with the download and is available in the documentation directory.

##Usage

Whilst the extensive documentation covers just about everything, here are a few examples of what you can do.

###Configuring PHPVideoToolkit

PHPVideoToolkit requires some basic configuration and is one through the Config class. The Config class is then used in the constructor of most PHPVideoToolkit classes. Any child object initialised within an already configured class will inherit the configuration options of the parent.

```php
namespace PHPVideoToolkit;

$config = new \PHPVideoToolkit\Config(array(
	'temp_directory' => './tmp',
	'ffmpeg' => '/opt/local/bin/ffmpeg',
	'ffprobe' => '/opt/local/bin/ffprobe',
	'yamdi' => '/opt/local/bin/yamdi',
	'qt-faststart' => '/opt/local/bin/qt-faststart',
));
```

If a config object is not defined and supplied to the PHPVideoToolkit classes, then a default Config object is created and assigned to the class.

Every example below uses ```$config``` as the configuration object.
###Accessing Data About FFmpeg

Simple demonstration about how to access information about FfmpegParser object.

```php
namespace PHPVideoToolkit;

$ffmpeg = new FfmpegParser($config);
$is_available = $ffmpeg->isAvailable(); // returns boolean
$ffmpeg_version = $ffmpeg->getVersion(); // outputs something like - array('version'=>1.0, 'build'=>null)
	
```
###Accessing Data About media files

Simple demonstration about how to access information about media files using the MediaParser object.

```php
namespace PHPVideoToolkit;

$parser = new MediaParser($config);
$data = $parser->getFileInformation('BigBuckBunny_320x180.mp4');
echo '<pre>'.print_r($data, true).'</pre>';
	
```
###PHPVideoToolkit Timecodes
PHPVideoToolkit utilises Timecode objects when extracting data such as duration or start points, or when extracting portions of a media file. They are fairly simple to understand. All of the example timecodes created below are the same time. 

```php
namespace PHPVideoToolkit;

$timecode = new Timecode(102.34);
$timecode = new Timecode(102.34, Timecode::INPUT_FORMAT_SECONDS);
$timecode = new Timecode(1.705666667, Timecode::INPUT_FORMAT_MINUTES);
$timecode = new Timecode(.028427778, Timecode::INPUT_FORMAT_HOURS);
$timecode = new Timecode('00:01:42.34', Timecode::INPUT_FORMAT_TIMECODE);

```

You can manipulate timecodes fairly simply.

```php
namespace PHPVideoToolkit;

$timecode = new Timecode('00:01:42.34', Timecode::INPUT_FORMAT_TIMECODE);
$timecode->hours += 15; // 15:01:42.34
$timecode->seconds -= 54125.5; // 00:00:18.84
$timecode->milliseconds -= 18840; // 00:00:00.00

// ...

$timecode->setSeconds(193.7);
echo $timecode; // Outputs '00:03:13.70'

// ...

$timecode->setTimecode('12:45:39.01');
echo $timecode->total_seconds; // Outputs 45939.01
echo $timecode->seconds; // Outputs 39

```

It's very important to note, as in the last example, that there is a massive difference between accessing ```$timecode->seconds``` and ```$timecode->total_seconds```. `seconds` is the number of seconds in the remaining minute of the timecode. `total_seconds` is the total number of seconds of the timecode. The same logic applies to minutes, hours, milliseconds and theire total_ prefixed counterparts.

###Extract a Single Frame of a Video

The code below extracts a frame from the video at the 40 second mark.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4', $config);
$output = $video->extractFrame(new Timecode(40))
	   			->save('./output/big_buck_bunny_frame.jpg');
```
###Extract Multiple Frames from a Segment of a Video

The code below extracts frames at the parent videos' frame rate from between 40 and 50 seconds. If the parent video has a frame rate of 24 fps then 240 images would be extracted from this code.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4', $config);
$output = $video->extractFrames(new Timecode(40), new Timecode(50))
	   			->save('./output/big_buck_bunny_frame_%timecode.jpg');
```

###Extract Multiple Frames of a Video at 1 frame per second

There are two ways you can export at a differing frame rate from that of the parent video. The first is to use an output format to set the video frame rate.

```php
namespace PHPVideoToolkit;

$output_format = new ImageFormat_Jpeg('output');

/*
OR 

$output_format = new VideoFormat('output', $config);
$output_format->setFrameRate(1);
// optionaly also set the video and output format, however if you use the ImageFormat_Jpeg 
// output format object this is automatically done for you. If you do not add below, FFmpeg
// automatically guesses from your output file extension which format and codecs you wish to use.
$output_format->setVideoCodec('mjpeg')
			  ->setFormat('image2');

*/

$video  = new Video('BigBuckBunny_320x180.mp4', $config);
$output = $video->extractFrames(null, new Timecode(50)) // if null then the extracted segment starts from the begining of the video
	   			->save('./output/big_buck_bunny_frame_%timecode.jpg', $output_format);
```

The second is to use the $force_frame_rate option of the extractFrames function.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4', $config);
$output = $video->extractFrames(new Timecode(50), null, 1) // if null then the extracted segment goes from the start timecode to the end of the video
	   			->save('./output/big_buck_bunny_frame_%timecode.jpg');
```

###Extracting an Animated Gif
Now, FFmpeg's animated gif support is a pile of doggy do do. I can't understand why. However what PHPVideoToolkit does is bypass the native gif exporting of FFmpeg and provide it's own much better alternative.

There are several options available to you when exporting an animated gif. You can use Gifsicle, Imagemagicks convert, or native PHP GD with the symbio/gif-creator composer library.

For high quality, but very slow encoding a combination of Gifsicle with Convert pre processing is suggested, alternatively for a quicker encode but lower quality, you can use native PHP GD or Convert. The examples below show you how to differentiate between the different methods.

Regards to performance. High frame rates greatly impact how fast a high quality encoding completes. It's suggested that if you need a high quality animated gif, that you limit your frame rate to around 5 frames per second.

**High Quality**

*Gifsicle with Imagemagick Convert*
```php
namespace PHPVideoToolkit;

$config->convert = '/opt/local/bin/convert';
$config->gif_transcoder = 'gifsicle';

$output_path = './output/big_buck_bunny.gif';

$output_format = \PHPVideoToolkit\Format::getFormatFor($output_path, $config, 'ImageFormat');
$output_format->setVideoFrameRate(5);
		
$video = new \PHPVideoToolkit\Video('media/BigBuckBunny_320x180.mp4', $config);
$output = $video->extractSegment(new \PHPVideoToolkit\Timecode(10), new \PHPVideoToolkit\Timecode(20))
				->save($output_path, $output_format);
	   			
```

**Quick Encoding, but lower quality (still better than FFmpeg mind)**

The examples below are listed in order of performance.

*Imagemagick Convert*
```php
namespace PHPVideoToolkit;

$config->gif_transcoder = 'convert';

$output_path = './output/big_buck_bunny.gif';

$output_format = \PHPVideoToolkit\Format::getFormatFor($output_path, $config, 'ImageFormat');
$output_format->setVideoFrameRate(5);
		
$video = new \PHPVideoToolkit\Video('media/BigBuckBunny_320x180.mp4', $config);
$output = $video->extractSegment(new \PHPVideoToolkit\Timecode(10), new \PHPVideoToolkit\Timecode(20))
				->save($output_path, $output_format);
	   			
```

*Native PHP GD with symbio/gif-creator library*
```php
namespace PHPVideoToolkit;

$config->gif_transcoder = 'php';

$output_path = './output/big_buck_bunny.gif';

$output_format = \PHPVideoToolkit\Format::getFormatFor($output_path, $config, 'ImageFormat');
$output_format->setVideoFrameRate(5);
		
$video = new \PHPVideoToolkit\Video('media/BigBuckBunny_320x180.mp4', $config);
$output = $video->extractSegment(new \PHPVideoToolkit\Timecode(10), new \PHPVideoToolkit\Timecode(20))
				->save($output_path, $output_format);
	   			
```

*Gifsicle with native PHP GD*
```php
namespace PHPVideoToolkit;

$config->convert = null; // This disables the imagemagick convert path so gifsicle transcoder falls back to GD
$config->gif_transcoder = 'gifsicle';

$output_path = './output/big_buck_bunny.gif';

$output_format = \PHPVideoToolkit\Format::getFormatFor($output_path, $config, 'ImageFormat');
$output_format->setVideoFrameRate(5);
		
$video = new \PHPVideoToolkit\Video('media/BigBuckBunny_320x180.mp4', $config);
$output = $video->extractSegment(new \PHPVideoToolkit\Timecode(10), new \PHPVideoToolkit\Timecode(20))
				->save($output_path, $output_format);
	   			
```

###Extracting Audio or Video Channels from a Video

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4', $config);
$output = $video->extractAudio()->save('./output/big_buck_bunny.mp3');
// $output = $video->extractVideo()->save('./output/big_buck_bunny.mp4');
	   			
```

###Extracting a Segment of an Audio or Video file

The code below extracts a portion of the video at the from 2 minutes 22 seconds to 3 minutes (ie 180 seconds). *Note the different settings for constructing a timecode.* The timecode object can accept different formats to create a timecode from.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4', $config);
$output = $video->extractSegment(new Timecode('00:02:22.0', Timecode::INPUT_FORMAT_TIMECODE), new Timecode(180))
	   			->save('./output/big_buck_bunny.mp4');
```
###Spliting a Audio or Video file into multiple parts

There are multiple ways you can configure the split parameters. If an array is supplied as the first argument. It must be an array of either, all Timecode instances detailing the timecodes at which you wish to split the media, or all integers. If integers are supplied the integers are treated as frame numbers you wish to split at. You can however also split at even intervals by suppling a single integer as the first paramenter. That integer is treated as the number of seconds that you wish to split at. If you have a video that is 3 minutes 30 seconds long and set the split to 60 seconds, you will get 4 videos. The first three will be 60 seconds in length and the last would be 30 seconds in length.

The code below splits a video into multiple of equal length of 45 seconds each. 

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4', $config);
$output = $video->split(45)
	   			->save('./output/big_buck_bunny_%timecode.mp4');
```
###Purging and then adding Meta Data

Unfortunately there is no way using FFmpeg to add meta data without re-encoding the file. There are other tools that can do that though, however if you wish to write meta data to the media during encoding you can do so using code like the example below.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4', $config);
$output = $video->purgeMetaData()
				->setMetaData('title', 'Hello World')
	   			->save('./output/big_buck_bunny.mp4');
```
###Non-Blocking Saves

The default/main save() function blocks PHP untill the encoding process has completed. This means that depending on the size of the media you are encoding it could leave your script running for a long time. To combat this you can call saveNonBlocking() to start the encoding process without blocking PHP.

However there are some caveats you need to be aware of before doing so. Once the non blocking process as started, if your PHP script closes PHPVideoToolkit can not longer "tidy up" temporary files or perform dynamic renaming of %index or %timecode output files. All repsonsibility is handed over to you. Of course, if you leave the PHP script open untill the encode has finished PHPVideoToolkit will do everything for you.

The code below is an example of how to manage a non-blocking save.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4', $config);
$process = $video->saveNonBlocking('./output/big_buck_bunny.mov');

// do something else important, db queries etc

while($process->isCompleted() === false)
{
	// do something more stuff in a loop.
	// doesn't have to be a loop, just an example.
	
	sleep(0.5);
}

if($process->hasError() === true)
{
	// an error was encountered, do something with it.
}
else
{
	// encoding has completed and no error was detected so 
	// we can get the output from the process.
	$output = $process->getOutput();
}

```

###Encoding with Progress Handlers

Whilst the code above from Non-Blocking Saves looks like it is a progress handler (and it is in a sense, but it doesn't provide data on the encode), progress handlers provide much more detailed information about the current encoding process.

PHPVideoToolkit allows you to monitor the encoding process of FFmpeg. This is done by using ProgressHandler objects. There are two types of progress handlers. ProgressHandlerNative and ProgressHandlerOutput. If your copy of FFmpeg is recent you will be able to use ProgressHandlerNative which uses FFmpegs '-progress' command to provide data, where was ProgressHandlerOutput relies on only the output of the command line buffer. Apart from that difference both handlers return the same data and act in the same way.

Progress Handlers can be made to block PHP or can be used in a non blocking fashion. They can even be utilized to work from a seperate script once the encoding has been initialised. However for purposes of this example the progress handlers are in the same script essentially blocking the PHP process. Again however, the two examples shown function very differently.

**Example 1. Callback in the handler constructor**

This example supplies the progress callback handler as a paramater to the constructor. This function is then called (every second, by default). Creating the callback in this way will block PHP and cannot be assigned as a progress handler when calling saveNonBlocking().

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4', $config);

$progress_handler = new ProgressHandlerNative(function($data)
{
	echo '<pre>'.print_r($data, true).'</pre>';
});

$output = $video->purgeMetaData()
				->setMetaData('title', 'Hello World')
	   			->save('./output/big_buck_bunny.mp4', null, Video::OVERWRITE_EXISTING, $progress_handler);
```

**Example 2. Probing the handler**

This example initialises a handler but does not supply a callback function. Instead you create your own method for creating a "progress loop" (or similar) and instead just call probe() on the handler.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4', $config);

$progress_handler = new ProgressHandlerNative();

$output = $video->purgeMetaData()
				->setMetaData('title', 'Hello World')
	   			->saveNonBlocking('./output/big_buck_bunny.mp4', null, Video::OVERWRITE_EXISTING, $progress_handler);
				
while($progress_handler->completed !== true)
{
	// note setting true in probe() automatically tells the probe to wait after the data is returned.
 	echo '<pre>'.print_r($progress_handler->probe(true), true).'</pre>';
}
				
```

So you see whilst the two examples look very similar and both block PHP, the second example does not need to block at all.


###Accessing Executed Commands and the Command Line Buffer

There may be instances where things go wrong and PHPVideoToolkit hasn't correctly prevented or reported any encoding/decoding errors, or, you may just want to log what is going on. You can access any executed commands and the command lines output fairly simply as the example below shows.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4', $config);
$output = $video->save('./output/big_buck_bunny.mov');
$process = $video->getProcess();

echo 'Expected Executed Command<br />';
echo '<pre>'.$process->getExecutedCommand().'</pre>';

echo 'Expected Command Line Buffer<br />';
echo '<pre>'.$process->getBuffer().'</pre>';
```

It's important to note, the the ExecBuffer object actually manipulates the raw command string given to it by the FfmpegProcess object. This is done so that the ExecBuffer can successfully track errors and process completion. The data returned by getExecutedCommand() and getBuffer() are values that are expected but not actual.

To get the actual executed command and buffer you can use the following.

```php
echo 'Actual Executed Command<br />';
echo '<pre>'.$process->getExecutedCommand(true).'</pre>';

echo 'Actual Command Line Buffer<br />';
echo '<pre>'.$process->getRawBuffer().'</pre>';
```

###Supplying custom commands

Because FFmpeg has a specific order in which certain commands need to be added there are a few functions you should be aware of. First of the code below shows you how to access the code FfmpegProcess object. The process object is itself a wrapper around the ProcessBuilder (helps to build queries) and ExceBuffer (executes and controls the query) objects.

The process object is passed by reference so any changes to the object are also made within the Video object.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4', $config);
$process = $video->getProcess();
				
```

Now you have access to the process object you can add specific commands to it. 

```php
// ... continued from above

$process->addPreInputCommand('-custom-command');
$process->addCommand('-custom-command-with-arg', 'arg value');
$process->addPostOutputCommand('-output-command', 'another value');
				
```

Now all of the example commands above will cause FFmpeg to fail, and they are just to illustrate a point.

- The function addPreInputCommand() adds commands to be given before the input command (-i) is added to the command string.
- The function addCommand() adds commands to be given after the input command (-i) is added to the command string.
- The function addPostOutputCommand() adds commands to be given after the output file is added to the command string.

To help explain it further, here is a simplified command string using the above custom commands.

```
/opt/bin/local/ffmpeg -custom-command -i '/your/input/file.mp4' -custom-command-with-arg 'arg value' '/your/output/file.mp4' -output-command 'another value'
```

###Imposing a processing timelimit

You may wish to impose a processing timelimit on encoding. There are various reasons for doing this and should be self explanitory. FFmpeg supplies a command to be able to do this and can be invoked like so...

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4', $config);

$process = $video->getProcess();
$process->setProcessTimelimit(10); // in seconds

try
{
	$video->save('output.mp4');
}
catch(FfmpegProcessOutputException $e)
{
	echo $e->getMessage(); // Imposed time limit (10 seconds) exceeded.
}
				
```






