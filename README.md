# PHPVideoToolkit V2...

...is a set of PHP classes aimed to provide a modular, object oriented and accessible interface for interacting with videos and audio through FFmpeg.

PHPVideoToolkit also provides FFmpeg-PHP emulation in pure PHP so you wouldn't need to compile and install the FFmpeg-PHP module, you only require FFmpeg and PHPVideoToolkit. As FFmpeg-PHP has not been updated since 2007 using FFmpeg-PHP with a new version of FFmpeg can often break FFmpeg-PHP. Using PHPVideoToolkits' emulation of FFmpeg-PHP's functionality allows you to upgrade FFmpeg without worrying about breaking functionality of provided through the FFmpeg-PHP API.

**IMPORTANT** PHPVideoToolkit has only been tested with v1.1.2 of FFmpeg. Whilst the majority of functionality should work regardless of your version of FFmpeg I cannot guarantee it. If you find a bug or have a patch please open a ticket or submit a pull request on https://github.com/buggedcom/phpvideotoolkit-v2

### Table of Contents

- [License](#license)
- [Documentation](#documentation)
- [Latest Changes](#latest-changes)
- [Usage](#usage)
- [Configuring PHPVideoToolkit](#configuring-phpvideotoolkit)
- [Accessing Data About FFmpeg](#accessing-data-about-ffmpeg)
- [Accessing Data About media files](#accessing-data-about-media-files)
- [PHPVideoToolkit Timecodes](#phpvideotoolkit-timecodes)
- [PHPVideoToolkit Output Formats](#phpvideotoolkit-output-formats)
- [Extract a Single Frame of a Video](#extract-a-single-frame-of-a-video)
- [Extract Multiple Frames from a Segment of a Video](#extract-multiple-frames-from-a-segment-of-a-video)
- [Extract Multiple Frames of a Video at 1 frame per second](#extract-multiple-frames-of-a-video-at-1-frame-per-second)
- [Extract Multiple Frames of a Video at 1 frame every 'x' seconds](#extract-multiple-frames-of-a-video-at-1-frame-every-x-seconds)
- [Caveats of Extracting Multiple Frames](#caveats-of-extracting-multiple-frames)
- [Combining Multiple Images and Audio to form a Video](#combining-multiple-images-and-audio-to-form-a-video)
- [Extracting an Animated Gif](#extracting-an-animated-gif)
- [Resizing Video and Images](#resizing-video-and-images)
- [Extracting Audio or Video Channels from a Video](#extracting-audio-or-video-channels-from-a-video)
- [Extracting a Segment of an Audio or Video file](#extracting-a-segment-of-an-audio-or-video-file)
- [Splitting a Audio or Video file into multiple parts](#splitting-a-audio-or-video-file-into-multiple-parts)
- [Purging and then adding Meta Data](#purging-and-then-adding-meta-data)
- [Changing Codecs of the audio or video stream](#changing-codecs-of-the-audio-or-video-stream)
- [Non-Blocking Saves](#non-blocking-saves)
- [Encoding with Progress Handlers](#encoding-with-progress-handlers)
- [Information Available to the Progress Handlers](#information-available-to-the-progress-handlers)
- [Encoding Multiple Output Files](#encoding-multiple-output-files)
- [Accessing Executed Commands and the Command Line Buffer](#accessing-executed-commands-and-the-command-line-buffer)
- [Supplying custom commands](#supplying-custom-commands)
- [Imposing a processing time limit](#imposing-a-processing-time-limit)
- [Forcing a specific output format whilst using a silly file extension](#forcing-a-specific-output-format-whilst-using-a-silly-file-extension)

## License

PHPVideoToolkit Copyright (c) 2008-2014 Oliver Lillie

DUAL Licensed under MIT and GPL v2

See LICENSE.md for more details.

## Documentation

Extensive documentation and examples are bundled with the download and is available in the documentation directory.

## Latest Changes

**[2.2.0-beta]** [10.04.2014]

WARNING: Potential code breaking changes across the board. Please do not upgrade existing stable scripts to this codebase. Please use 2.1.5 or below for stability.
- merged in multi-output branch so that the master branch now supports multi output from ffmpeg.
- fixed far too many other bugs to mention.

[Full changelog](https://github.com/buggedcom/phpvideotoolkit-v2/blob/master/CHANGELOG.md)

## Usage

Whilst the extensive documentation covers just about everything (to be honest there are only a few pages in the documentation as I'm too busy to write too much of it - but the examples below are pretty good), here are a few examples of what you can do.

### Configuring PHPVideoToolkit

PHPVideoToolkit requires some basic configuration and is one through the Config class. The Config class is then used in the constructor of most PHPVideoToolkit classes. Any child object initialised within an already configured class will inherit the configuration options of the parent.

```php
namespace PHPVideoToolkit;

$config = new Config(array(
	'temp_directory' => './tmp',
	'ffmpeg' => '/opt/local/bin/ffmpeg',
	'ffprobe' => '/opt/local/bin/ffprobe',
	'yamdi' => '/opt/local/bin/yamdi',
	'qtfaststart' => '/opt/local/bin/qt-faststart',
	'cache_driver' => 'InTempDirectory',
), true);
```

Take special note of the second parameter ```true```. If set as true then the related Config object is set as the default config instance. This means that once set as the default instance you do not need to supply the Config object to the other PHPVideoToolkit class constructors. If a config object is not defined and supplied to the PHPVideoToolkit classes, then a default Config object is created and assigned to the class.

Every example below assumes that the Config object has been set as the default config object prior in the execution so there is no need to supply config to each example.

### Accessing Data About FFmpeg

Simple demonstration about how to access information about FfmpegParser object.

```php
namespace PHPVideoToolkit;

$ffmpeg = new FfmpegParser();
$is_available = $ffmpeg->isAvailable(); // returns boolean
$ffmpeg_version = $ffmpeg->getVersion(); // outputs something like - array('version'=>1.0, 'build'=>null)
	
```
### Accessing Data About media files

Simple demonstration about how to access information about media files using the MediaParser object.

```php
namespace PHPVideoToolkit;

$parser = new MediaParser();
$data = $parser->getFileInformation('BigBuckBunny_320x180.mp4');
echo '<pre>'.print_r($data, true).'</pre>';
	
```
### PHPVideoToolkit Timecodes
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

It's very important to note, as in the last example, that there is a massive difference between accessing ```$timecode->seconds``` and ```$timecode->total_seconds```. `seconds` is the number of seconds in the remaining minute of the timecode. `total_seconds` is the total number of seconds of the timecode. The same logic applies to minutes, hours, milliseconds and their total_ prefixed counterparts.

### PHPVideoToolkit Output Formats

PHPVideoToolkit contains a base class `Format`. This class is extended by three other important base classes called `AudioFormat`, `VideoFormat` and `ImageFormat`. They extend as follows: Format > AudioFormat > VideoFormat > ImageFormat. This allows each of the later formats to inherit functionality from the previous format.

FFmpeg allows you to set certain import format parameters to the input media. As such these Format classes work for both input and output formatting of media. For the most part, unless you need to do something very specific you do not need to worry about setting an input format for the media you wish to put into FFmpeg. So this documentation will not explain input formatting.

Generally speaking if you are just transcoding from one format to another, you do not even need to worry about supplying an output format either. PHPVideoToolkit will best guess the output format you require and then apply it magically when you call 'save' to encode the media. To this end there are specific Audio, Image and Video formats you can use. These are listed below. If you have specific settings for a specific file output please feel free to create your own media specific output formats and submit a pull request and I will include them in the bundle. These media specific formats are listed below.

_Audio_

- `AudioFormat_Acc`
- `AudioFormat_Flac`
- `AudioFormat_Mp3`
- `AudioFormat_Oga`
- `AudioFormat_Wav`

_Image_

- `ImageFormat_Bmp`
- `ImageFormat_Gif`
- `ImageFormat_Jpeg`
- `ImageFormat_Png`
- `ImageFormat_Ppm`

_Video_

- `VideoFormat_3gp`
- `VideoFormat_Flv`
- `VideoFormat_H264`
- `VideoFormat_Mkv`
- `VideoFormat_Mp4`
- `VideoFormat_Ogg`
- `VideoFormat_Webm`
- `VideoFormat_Wmv`
- `VideoFormat_Wmv3`

For the most part all these format specific Format classes do is set the neccessary codecs and settings required to generate the desired output, however, formats like `VideoFormat_Mp4`, `VideoFormat_H264` or `VideoFormat_Flv` contain further functionality and make use of encoding completion callbacks to further process the media after FFmpeg has finished encoding them. For example the flv format runs the resulting output from FFmpeg through the yamdi server library to inject meta data, or the mp4 format uses qtfaststart to create a fast start streaming mp4.

So getting to the bit you will most likely use... The formatting of outputted media. 

_AudioFormat_

[AudioFormat](https://github.com/buggedcom/phpvideotoolkit-v2/blob/master/src/PHPVideoToolkit/AudioFormat.php) and child classes thereof have the following functions available.

- disableAudio/enableAudio
- setAudioCodec
- setAudioBitrate
- setAudioSampleFrequency
- setAudioChannels
- setVolume
- setAudioQuality

_VideoFormat_

[VideoFormat](https://github.com/buggedcom/phpvideotoolkit-v2/blob/master/src/PHPVideoToolkit/VideoFormat.php) and child classes thereof have the following functions available.

- disableVideo/enableVideo
- setVideoCodec
- setVideoDimensions
- setVideoScale
- setVideoPadding
- setVideoAspectRatio
- setVideoFrameRate
- setVideoMaxFrames
- setVideoBitrate
- setVideoPixelFormat
- setVideoQuality
- setVideoRotation
- videoFlipVertical
- videoFlipHorizontal

_ImageFormat_

ImageFormat and the related child classes do not have any further functions.

**Basic usage**

Below is an example of a very simple manipulation of a video.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');

$output_format = new VideoFormat_Mp4();
// attempt to auto rotate the video to the correct orientation (ie mobile phone users - hurgygur)
$output_format->setVideoRotation(true)
			  ->setVideoFrameRate(10)
			  ->setVideoPixelFormat('rgb24')
			  ->setAudioSampleFrequency(44100);

$video->save('output.mp4', $output_format);
				
```


### Forcing a specific output format whilst using a silly file extension

Because of the advanced nature of the input and output formatters, if supplied you can encode a specific output, but use a silly (or custom) file extension. Not really sure why you would want to but it is possible.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');
$video->save('output.my_silly_custom_file_extension', new ImageFormat_Jpeg());
				
```

### Extract a Single Frame of a Video

The code below extracts a frame from the video at the 40 second mark.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');
$process = $video->extractFrame(new Timecode(40))
				->save('./output/big_buck_bunny_frame.jpg');
$output = $process->getOutput();
```
### Extract Multiple Frames from a Segment of a Video

The code below extracts frames at the parent videos' frame rate from between 40 and 50 seconds. If the parent video has a frame rate of 24 fps then 240 images would be extracted from this code.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');
$process = $video->extractFrames(new Timecode(40), new Timecode(50))
				->save('./output/big_buck_bunny_frame_%timecode.jpg');
$output = $process->getOutput();
```

### Extract Multiple Frames of a Video at 1 frame per second

There are two ways you can export at a differing frame rate from that of the parent video. The first is to use an output format to set the video frame rate.

```php
namespace PHPVideoToolkit;

$output_format = new ImageFormat_Jpeg();

/*
OR 

$output_format = new VideoFormat();
$output_format->setFrameRate(1);
// optionally also set the video and output format, however if you use the ImageFormat_Jpeg 
// output format object this is automatically done for you. If you do not add below, FFmpeg
// automatically guesses from your output file extension which format and codecs you wish to use.
$output_format->setVideoCodec('mjpeg')
			  ->setFormat('image2');

*/

$video  = new Video('BigBuckBunny_320x180.mp4');
$process = $video->extractFrames(null, new Timecode(50)) // if null then the extracted segment starts from the begining of the video
				->save('./output/big_buck_bunny_frame_%timecode.jpg', $output_format);
$output = $process->getOutput();
```

The second is to use the $force_frame_rate option of the extractFrames function.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');
$process = $video->extractFrames(new Timecode(50), null, 1) // if null then the extracted segment goes from the start timecode to the end of the video
				->save('./output/big_buck_bunny_frame_%timecode.jpg');
$output = $process->getOutput();
```

### Extract Multiple Frames of a Video at 1 frame every 'x' seconds

The code below uses the ```$force_frame_rate``` argument for ```$video->extractFrames()```, however the same 1/n notation can be used on ```$video_format->setFrameRate()```. This example will output 1 frame every 60 seconds of video.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');
$process = $video->extractFrames(new Timecode(40), new Timecode(50), '1/60')
				->save('./output/big_buck_bunny_frame_%timecode.jpg');
$output = $process->getOutput();
```

### Caveats of Extracting Multiple Frames

***IMPORTANT:*** It is important to note that if you exporting multiple frames a video you will not always get the expected amount of frames you would expect. This is down to the way FFmpeg treats timecodes. Take the example below into consideration.

```php
namespace PHPVideoToolkit;

$video = new \PHPVideoToolkit\Video($example_video_path);

$process = $video->extractSegment(new \PHPVideoToolkit\Timecode(10), new \PHPVideoToolkit\Timecode(20))
				->extractFrames(null, null, 1)
				->save('./output/%timecode.jpg', null, \PHPVideoToolkit\Media::OVERWRITE_EXISTING);

$output = $process->getOutput();
```

You may assume that looking at this example you will get 10 frames outputted because the segment being extracted is 10 seconds long. However you will actually only get 9 frames exported. This is because the end time frame is treated as a less than value rather than a less than and equal to value. So in pseudo code this is what is happening when frames are extracted.

```
current = 10;
end = 20;
while(current < end)
{
	extractFrame(current);
	current += 1;
}
```

So if we require 10 frames you must actually set your end timecode to a little over 20 seconds like so ```$video->extractSegment(new \PHPVideoToolkit\Timecode(10), new \PHPVideoToolkit\Timecode(20.1))```

### Combining Multiple Images and Audio to form a Video

Whilst PHPVideoToolkit does not natively support combing multiple images and audio into a video, it can still be achieved by add custom commands to the process object.

```php
$audio = new Audio('Ballad_of_the_Sneak.mp3');

$process = $audio->getProcess();
$process->addPreInputCommand('-framerate', '1/5');
$process->addPreInputCommand('-pattern_type', 'glob');
$process->addPreInputCommand('-i', 'images/*.jpg');
$process->addCommand('-pix_fmt', 'yuv420p');
$process->addCommand('-shortest', '');

$output_format = new VideoFormat();
$output_format->setVideoFrameRate('1/5');
$output_format->setVideoDimensions(320, 240);

$process = $audio->save('./output/my_homemade_video.mp4', $output_format, Media::OVERWRITE_EXISTING);

```

### Extracting an Animated Gif
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

$output_format = Format::getFormatFor($output_path, $config, 'ImageFormat');
$output_format->setVideoFrameRate(5);
		
$video = new Video('media/BigBuckBunny_320x180.mp4', $config);
$process = $video->extractSegment(new Timecode(10), new Timecode(20))
				->save($output_path, $output_format);
				
$output = $process->getOutput();
```

**Quick Encoding, but lower quality (still better than FFmpeg mind)**

The examples below are listed in order of performance.

*Imagemagick Convert*
```php
namespace PHPVideoToolkit;

$config->gif_transcoder = 'convert';

$output_path = './output/big_buck_bunny.gif';

$output_format = Format::getFormatFor($output_path, $config, 'ImageFormat');
$output_format->setVideoFrameRate(5);
		
$video = new Video('media/BigBuckBunny_320x180.mp4', $config);
$process = $video->extractSegment(new Timecode(10), new Timecode(20))
				->save($output_path, $output_format);
				
$output = $process->getOutput();
```

*Native PHP GD with symbio/gif-creator library*
```php
namespace PHPVideoToolkit;

$config->gif_transcoder = 'php';

$output_path = './output/big_buck_bunny.gif';

$output_format = Format::getFormatFor($output_path, $config, 'ImageFormat');
$output_format->setVideoFrameRate(5);
		
$video = new Video('media/BigBuckBunny_320x180.mp4', $config);
$process = $video->extractSegment(new Timecode(10), new Timecode(20))
				->save($output_path, $output_format);
				
$output = $process->getOutput();
```

*Gifsicle with native PHP GD*
```php
namespace PHPVideoToolkit;

$config->convert = null; // This disables the imagemagick convert path so gifsicle transcoder falls back to GD
$config->gif_transcoder = 'gifsicle';

$output_path = './output/big_buck_bunny.gif';

$output_format = Format::getFormatFor($output_path, $config, 'ImageFormat');
$output_format->setVideoFrameRate(5);
		
$video = new Video('media/BigBuckBunny_320x180.mp4', $config);
$process = $video->extractSegment(new Timecode(10), new Timecode(20))
				->save($output_path, $output_format);
				
$output = $process->getOutput();
```

### Resizing Video and Images

In order to resize output video and imagery output you need to supply an [output format](#phpvideotoolkit-output-formats) to the save function you call. The below snippet is an example of resizing a video, however you would use same function call to `setVideoDimensions` just instead of a `VideoFormat` object you would use an `ImageFormat` object.

```php

namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');

$output_format = new VideoFormat();
$output_format->setVideoDimensions(160, 120);

$video->save('BigBuckBunny_160x120.3gp', $output_format);

```

### Extracting Audio or Video Channels from a Video

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');
$process = $video->extractAudio()->save('./output/big_buck_bunny.mp3');
// $process = $video->extractVideo()->save('./output/big_buck_bunny.mp4');
				
$output = $process->getOutput();
```

### Extracting a Segment of an Audio or Video file

The code below extracts a portion of the video at the from 2 minutes 22 seconds to 3 minutes (ie 180 seconds). *Note the different settings for constructing a timecode.* The timecode object can accept different formats to create a timecode from.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');
$process = $video->extractSegment(new Timecode('00:02:22.0', Timecode::INPUT_FORMAT_TIMECODE), new Timecode(180))
				->save('./output/big_buck_bunny.mp4');
$output = $process->getOutput();
```
### Splitting a Audio or Video file into multiple parts

There are multiple ways you can configure the split parameters. If an array is supplied as the first argument. It must be an array of either, all Timecode instances detailing the timecodes at which you wish to split the media, or all integers. If integers are supplied the integers are treated as frame numbers you wish to split at. You can however also split at even intervals by suppling a single integer as the first parameter. That integer is treated as the number of seconds that you wish to split at. If you have a video that is 3 minutes 30 seconds long and set the split to 60 seconds, you will get 4 videos. The first three will be 60 seconds in length and the last would be 30 seconds in length.

The code below splits a video into multiple of equal length of 45 seconds each. 

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');
$process = $video->split(45)
				->save('./output/big_buck_bunny_%timecode.mp4');
$output = $process->getOutput();
```
### Purging and then adding Meta Data

Unfortunately there is no way using FFmpeg to add meta data without re-encoding the file. There are other tools that can do that though, however if you wish to write meta data to the media during encoding you can do so using code like the example below.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');
$process = $video->purgeMetaData()
				->setMetaData('title', 'Hello World')
				->save('./output/big_buck_bunny.mp4');
$output = $process->getOutput();
```
### Changing Codecs of the audio or video stream

By default PHPVideoToolkit uses the file extension of the output file to automatically generate the required ffmpeg settings (if any) of your desired file format. However if you want to specify different codecs or settings, it is ncessesary to specify them within an output format container. There are three different format objects you can use, depending on the format of your output. They are AudioFormat, VideoFormat and ImageFormat.

Note; the examples below are for demonstration purposes only and _may not work_.

*Changing the audio and video codecs of an outputted video*
```php
namespace PHPVideoToolkit;

$output_path = './output/big_buck_bunny.mpeg';

$output_format = new VideoFormat();
$output_format->setAudioCodec('acc')
			  ->setVideoCodec('ogg');

$video = new Video('media/BigBuckBunny_320x180.mp4');
$process = $video->save($output_path, $output_format);
$output = $process->getOutput();
```

*Changing the audio codec of an audio export*
```php
namespace PHPVideoToolkit;

$output_path = './output/big_buck_bunny.mp3';

$output_format = new AudioFormat();
$output_format->setAudioCodec('acc');

$video = new Video('media/BigBuckBunny_320x180.mp4');
$process = $video->save($output_path, $output_format);

$output = $process->getOutput();
```
### Non-Blocking Saves

The default/main save() function blocks PHP until the encoding process has completed. This means that depending on the size of the media you are encoding it could leave your script running for a long time. To combat this you can call saveNonBlocking() to start the encoding process without blocking PHP.

However there are some caveats you need to be aware of before doing so. Once the non blocking process as started, if your PHP script closes PHPVideoToolkit can not longer "tidy up" temporary files or perform dynamic renaming of %index or %timecode output files. All responsibility is handed over to you. Of course, if you leave the PHP script open until the encode has finished PHPVideoToolkit will do everything for you.

The code below is an example of how to manage a non-blocking save.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');
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

### Encoding with Progress Handlers

Whilst the code above from Non-Blocking Saves looks like it is a progress handler (and it is in a sense, but it doesn't provide data on the encode), progress handlers provide much more detailed information about the current encoding process.

PHPVideoToolkit allows you to monitor the encoding process of FFmpeg. This is done by using ProgressHandler objects. There are three types of progress handlers. 

- ProgressHandlerNative
- ProgressHandlerOutput
- ProgressHandlerPortable

ProgressHandlerNative and ProgressHandlerOutput work and function in the same way, however one uses a native ffmpeg command, and the out outputs ffmpeg output buffer to a temp file. If your copy of FFmpeg is recent you will be able to use ProgressHandlerNative which uses FFmpegs '-progress' command to provide data. The handlers return slightly differing amounts of data, and the more accurate and verbose of the two is ProgressHandlerOutput and it is recommended that you use that progress handler between the two. However they do both return the same essential data and act in the same way and there is no real need to prioritise one over another unless you version of ffmpeg does not support '-progress'. If it doesn't then when you initialise the ProgressHandlerNative an exception will be thrown. Between the two I personally recommend ProgressHandlerOutput because of the more verbose nature of the data available.

The third type of handler ProgressHandlerPortable (shown in example 3 below) operates somewhat differently and is specifically design to work with separate HTTP requests or threads. ProgressHandlerPortable can be initiated in a different script entirely, supplied with the PHPVideoToolkit portable process id and then probed independently of the encoding script. This allows developers to decouple encoding and encoding status scripts.

Progress Handlers can be made to block PHP or can be used in a non blocking fashion. They can even be utilised to work from a separate script once the encoding has been initialised. However for purposes of the first two examples the progress handlers are in the same script essentially blocking the PHP process. Again however, the first two examples shown function very differently.

**Example 1. Callback in the handler constructor**

This example supplies the progress callback handler as a parameter to the constructor. This function is then called (every second, by default). Creating the callback in this way will block PHP and cannot be assigned as a progress handler when calling saveNonBlocking().

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');

$progress_handler = new ProgressHandlerNative(function($data)
{
	echo '<pre>'.print_r($data, true).'</pre>';
});

$process = $video->purgeMetaData()
				->setMetaData('title', 'Hello World')
				->save('./output/big_buck_bunny.mp4', null, Video::OVERWRITE_EXISTING, $progress_handler);
$output = $process->getOutput();
```

**Example 2. Probing the handler**

This example initialises a handler but does not supply a callback function. Instead you create your own method for creating a "progress loop" (or similar) and instead just call probe() on the handler.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');

$progress_handler = new ProgressHandlerNative(null);

$process = $video->purgeMetaData()
				->setMetaData('title', 'Hello World')
				->saveNonBlocking('./output/big_buck_bunny.mp4', null, Video::OVERWRITE_EXISTING, $progress_handler);
				
while($progress_handler->completed !== true)
{
	// note setting true in probe() automatically tells the probe to wait after the data is returned.
	echo '<pre>'.print_r($progress_handler->probe(true), true).'</pre>';
}
				
$output = $process->getOutput();

```

So you see whilst the two examples look very similar and both block PHP, the second example does not need to block at all.

**Example 3. Non Blocking Save with Remote Progress Handling**

This example (a better example is found in /examples/progress-handler-portability.php) shows that a non blocking save can be made in one request, and then subsequent requests (i.e. ajax) can be made to a different script to probe the encoding progress.

**IMPORTANT:** Please remember that any ```Config``` object used in either of the below scripts must be the same in BOTH scripts in order to ensure the progress data can be retrieved in the progress script.

Encoding script:
```php

namespace PHPVideoToolkit;

session_start();

$video  = new Video('BigBuckBunny_320x180.mp4');
$process = $video->saveNonBlocking('./output/big_buck_bunny.mp4', null, Video::OVERWRITE_EXISTING);
				
$_SESSION['phpvideotoolkit_portable_process_id'] = $video->getPortableId();

```

Probing script:
```php

namespace PHPVideoToolkit;

session_start();

$handler = new ProgressHandlerPortable($_SESSION['phpvideotoolkit_portable_process_id']);

$probe = $handler->probe();

echo json_encode(array(
	'finished' => $probe['finished'], // true when the process has ended by interruption, error or success
	'completed' => $probe['completed'], // true when the process has ended with a successful encoding that encountered no errors.
	'percentage' => $probe['percentage']
));
exit;

```

**Progress Handler Caveats**

**1**: When encoding MP4s and having enabled qt-faststart usage either through setting ```\PHPVideoToolkit\Config->force_enable_qtfaststart = true;``` or ```\PHPVideoToolkit\VideoFormat_Mp4::enableQtFastStart()``` saves are put into blocking mode as processing with qt-faststart requires further exec calls. Similarly any encoding post processes such as when encoding FLVs will also convert a non blocking save into a blocking one.

**2**: When outputting files using %timecode or %index and using the ProgressHandlerPortable system it is not possible to currently automatically renaming the resulting temporary file output to their correct output filenames.

### Information Available to the Progress Handlers
All of the progress handlers outlined above returned the same information, albeit there are some minor differences as when some of the data is available, however the difference is negligle and for the purposes of this document they really do not matter. Below is a sample output of the data available from ```$progress_handler->probe();```

```
Array
(
	[error] => 
	[error_message] => 
	[started] => 1
	[finished] => 
	[completed] => 
	[interrupted] => 
	[status] => encoding
	[run_time] => 0.730171918869
	[percentage] => 11.0666666667
	[fps_avg] => 209.539693388
	[size] => 242kB
	[frame] => 153
	[duration] => PHPVideoToolkit\Timecode Object
		(
			[_total_frames:protected] => 
			[_total_milliseconds:protected] => 6640
			[_total_seconds:protected] => 6.64
			[_total_minutes:protected] => 0.110666666667
			[_total_hours:protected] => 0.00184444444444
			[_frames:protected] => 
			[_milliseconds:protected] => 0.64
			[_seconds:protected] => 6
			[_minutes:protected] => 0
			[_hours:protected] => 0
			[_frame_rate:protected] => 
		)

	[expected_duration] => PHPVideoToolkit\Timecode Object
		(
			[_total_frames:protected] => 
			[_total_milliseconds:protected] => 60000
			[_total_seconds:protected] => 60
			[_total_minutes:protected] => 1
			[_total_hours:protected] => 0.0166666666667
			[_frames:protected] => 
			[_milliseconds:protected] => 0
			[_seconds:protected] => 60
			[_minutes:protected] => 0
			[_hours:protected] => 0
			[_frame_rate:protected] => 
		)

	[fps] => 0.0
	[dup] => 
	[drop] => 
	[output_count] => 1
	[output_file] => /@Projects/PHPVideoToolkit/v2/git/examples/output/big_buck_bunny.3gp
	[input_count] => 1
	[input_file] => /@Projects/PHPVideoToolkit/v2/git/examples/media/BigBuckBunny_320x180.mp4
	[process_file] => /@Projects/PHPVideoToolkit/v2/git/examples/tmp/phpvideotoolkit_GsZ7FC
)
```

There are 4 different "status" booleans and one specific status code in the output that you should be aware of. They are ***started***, ***interrupted***, ***completed***, ***finished*** and ***status***.

**started** ***(boolean)***

This is set to true once the process file has been found by PHPVideoToolkit and the decode/encode process has been sent to FFmpeg.

**interrupted** ***(boolean)***

This is set to true if for some reason the server as stopped the encoding process prematurely. If this is ever encountered, it means your encoding process has failed and cannot be restarted other than attempting the encode again.

**completed** ***(boolean)*** 

This is set to true once FFmpeg has signaled that the encoding process has finished.

**finished** ***(boolean)*** 

This is set to true once PHPVideoToolkit has received the completion signal from the command line. This is typically speaking set after 'completed' is set to true. This is because after the encode has completed, FFmpeg outputs more information after the process has completed and then PHPVideoToolkit does a little tidying of temporary files or post process particular file formats.

**status** ***(constant/string)***

This is a value that is defined according to the values above using the following constants; `ProgressHandlerDefaultData::ENCODING_STATUS_PENDING`, `ProgressHandlerDefaultData::ENCODING_STATUS_DECODING`, `ProgressHandlerDefaultData::ENCODING_STATUS_ENCODING`, `ProgressHandlerDefaultData::ENCODING_STATUS_FINALISING`, `ProgressHandlerDefaultData::ENCODING_STATUS_COMPLETED`, `ProgressHandlerDefaultData::ENCODING_STATUS_FINISHED`, `ProgressHandlerDefaultData::ENCODING_STATUS_INTERRUPTED`,  and `ProgressHandlerDefaultData::ENCODING_STATUS_ERROR`. You'll notice there are more status than boolean flags. This is because the 'status' key is slightly more verbose. As a result the constant values are explained below.

`ProgressHandlerDefaultData::ENCODING_STATUS_PENDING`

This means that the process has not yet started decoding the input media. It is followed by...

`ProgressHandlerDefaultData::ENCODING_STATUS_DECODING`

This means that FFmpeg is currently decoding the input media. Which is then followed by...

`ProgressHandlerDefaultData::ENCODING_STATUS_ENCODING`

This means that FFmpeg is currently encoding the output files. This is followed by...

`ProgressHandlerDefaultData::ENCODING_STATUS_FINALISING`

This means that FFmpeg has reached the end of the encoding process and is currently tidying up and we are in the final stages of the encode. This is followed by...

`ProgressHandlerDefaultData::ENCODING_STATUS_COMPLETED`

Which means that the encoding process has completed but PHPVideoToolkit still requires a little moment to tidy up. 

`ProgressHandlerDefaultData::ENCODING_STATUS_FINISHED`

Then this is given it means the encoding process is totally complete and you can safely move/rename/use your end output media.

If anything as gone wrong in the encoding process you will get either  `ProgressHandlerDefaultData::ENCODING_STATUS_INTERRUPTED` or `ProgressHandlerDefaultData::ENCODING_STATUS_ERROR` at any point of the encoding process. Generally speaking if either of these constants are given both the ***error*** and ***error_message*** keys within the probed data will also be populated.

The other values returned via the probe data are explained below.

**run_time** ***(float)***

This is the total time in seconds that the process has taken.

**percentage** ***(float)***

This is the encoding completion percentage in the range of 0-100. 100 being the encode is complete.

**fps** ***(float)***

This is the current number of frames per second that are being processed.

**fps_avg** ***(float)***

This is the average number of frames per second that are being processed.

**frame** ***(integer)***

This is the current frame that is being processed.

**size** ***(string)***

This is the current size of the output media.

**duration** ***(PHPVideoToolkit\Timecode object)***

This is the current duration of the output media (if appropriate - as if you are outputting images only this will be `null`)

**expected_duration** ***(PHPVideoToolkit\Timecode object)***

This is the expected approimate value that the output media's duration will be. The final value held by 'duration' (above) will usually be a few microseconds different that this value.

**dup** ***(float)***

This value is the number of duplicate frames processed.

**drop** ***(float)***

This is the number of dropped frames.

**output_count** ***(integer)***

This value is the number of ouput files expected.

**output_file** ***(string/array)***

This value is either a) a path value as a string if the above 'output_count' is 1, or b) and array of path strings if the above 'output_count' is greated than 1.

**input_count** ***(integer)***

This value is the number of input files being used.

**input_file** ***(string/array)***

This value is either a) a path value as a string if the above 'input_count' is 1, or b) and array of path strings if the above 'input_count' is greated than 1.

**process_file** ***(string)***

This is a path string pointing to the current process file that the progress handler is using to read the data from.

### Encoding Multiple Output Files

FFmpeg allows you to [encode multiple output formats from a single command](https://trac.ffmpeg.org/wiki/Creating%20multiple%20outputs). PHPVideoToolkit allows you to perform this functionality as well. This functionality is essentially the same process as performing multiple saves, however has the added benefit of lower overhead because the input file only has to be read into memory once before the encoding takes place. It is recommended that you use this method if you are outputting more than one version of the media. There are of course several caveats when using this method however. 

When splitting files into multiple segments or extracting portions of a video the transformations that take place are performed on all the outputed media. An example of this functionality can be found in ```convert-to-multiple-output.php```. However a quick example is also shown below.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');

$multi_output = new MultiOutput();

$ogg_output = './output/big_buck_bunny.multi1.ogg';
$format = Format::getFormatFor($ogg_output, $config, 'VideoFormat');
$format->setVideoDimensions(VideoFormat::DIMENSION_SQCIF);
$multi_output->addOutput($ogg_output, $format);

$threegp_output = './output/big_buck_bunny.multi2.3gp';
$format = Format::getFormatFor($threegp_output, $config, 'VideoFormat');
$format->setVideoDimensions(VideoFormat::DIMENSION_XGA);
$multi_output->addOutput($threegp_output, $format);

$output = $video->save($multi_output, null, Media::OVERWRITE_EXISTING);

```

All progress handlers also work with multiple output, however the caveats outlined for the ProgressHandlerPortable still apply.

**IMPORTANT** Whilst this is technically possibly, depending on your server and the number of outputs you are generating, it can be quicker to simply chain the requests together instead. See the [chaining processes example](https://github.com/buggedcom/phpvideotoolkit-v2/blob/master/examples/chaining-processes.php) for more information on method chaining.

### Accessing Executed Commands and the Command Line Buffer

There may be instances where things go wrong and PHPVideoToolkit hasn't correctly prevented or reported any encoding/decoding errors, or, you may just want to log what is going on. You can access any executed commands and the command lines output fairly simply as the example below shows.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');
$process = $video->save('./output/big_buck_bunny.mov');

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

### Supplying custom commands

Because FFmpeg has a specific order in which certain commands need to be added there are a few functions you should be aware of. First of the code below shows you how to access the code FfmpegProcess object. The process object is itself a wrapper around the ProcessBuilder (helps to build queries) and ExceBuffer (executes and controls the query) objects.

The process object is passed by reference so any changes to the object are also made within the Video object.

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');
$process = $video->getProcess();

```

Now you have access to the process object you can add specific commands to it. 

```php
// ... continued from above

$process->addPreInputCommand('-custom-command');
$process->addCommand('-custom-command-with-arg', 'arg value');
$process->addPostOutputCommand('-output-command', 'another value');

// ... now save the output video

$video->save('./your/output/file.mp4');

```

Now all of the example commands above will cause FFmpeg to fail, and they are just to illustrate a point.

- The function addPreInputCommand() adds commands to be given before the input command (-i) is added to the command string.
- The function addCommand() adds commands to be given after the input command (-i) is added to the command string.
- The function addPostOutputCommand() adds commands to be given after the output file is added to the command string.

To help explain it further, here is a simplified command string using the above custom commands.

```
/opt/bin/local/ffmpeg -custom-command -i '/your/input/file.mp4' -custom-command-with-arg 'arg value' '/your/output/file.mp4' -output-command 'another value'
```

HOWEVER, there is an important caveat you need to be aware of, the above command is just and example to show you the position of the added commands. Using the same additional commands as above, the actual executed command looks like this:

```
((/opt/local/bin/ffmpeg '-custom-command' '-i' '/your/input/file.mp4' '-custom-command-with-arg' 'arg value' '-y' '-qscale' '4' '-f' 'mp4' '-strict' 'experimental' '-threads' '1' '-acodec' 'mp3' '-vcodec' 'h264' '/your/output/file.mp4' '-output-command' 'another value' && echo '<c-219970-52ea5f8c9ca9d-da39f7c51d495967dfec435dc91e2879>') || echo '<f-219970-52ea5f8c9ca9d-da39f7c51d495967dfec435dc91e2879>' '<c-219970-52ea5f8c9ca9d-da39f7c51d495967dfec435dc91e2879>' '<e-219970-52ea5f8c9ca9d-da39f7c51d495967dfec435dc91e2879>'$?) 2>&1 > '/tmp/phpvideotoolkit_lvsukB' 2>&1 &
```

### Imposing a processing timelimit

You may wish to impose a processing timelimit on encoding. There are various reasons for doing this and should be self explanatory. FFmpeg supplies a command to be able to do this and can be invoked like so...

```php
namespace PHPVideoToolkit;

$video  = new Video('BigBuckBunny_320x180.mp4');

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

