#PHPVideoToolkit V2...

...is a set of PHP classes aimed to provide a modular, object oriented and accessible interface for interacting with videos and audio through FFmpeg.

It also currently provides FFmpeg-PHP emulation in pure PHP so you wouldn't need to compile and install the module. As FFmpeg-PHP has not been updated since 2007 using FFmpeg-PHP with a new version of FFmpeg can often break the module. Using PHPVideoToolkits' emulation of FFmpeg-PHP's functionality allows you to upgrade FFmpeg without worrying about breaking existing funcitonality.

##Documentation

Extensive documentation and examples are bundled with the download and is available in the documentation directory.

##Usage

Whilst the extensive documentation covers just about everything, here are a few examples of what you can do.

###Extract a Single Frame of a Video

The code below extracts a frame from the video at the 40 second mark.

```php
namespace PHPVideoToolkit;

$video  = Factory::video('BigBuckBunny_320x180.mp4');
$output = $video->extractFrame(new Timecode(40))
	   			->save('./output/big_buck_bunny_frame.jpg');
```
###Extract Multiple Frames from a Segment of a Video

The code below extracts frames at the parent videos' frame rate from between 40 and 50 seconds. If the parent video has a frame rate of 24 fps then 240 images would be extracted from this code.

```php
namespace PHPVideoToolkit;

$video  = Factory::video('BigBuckBunny_320x180.mp4');
$output = $video->extractFrames(new Timecode(40), new Timecode(50))
	   			->save('./output/big_buck_bunny_frame_%timecode.jpg');
```

###Extract Multiple Frames of a Video at 1 frame per second

There are two ways you can export at a differing frame rate from that of the parent video. The first is to use an output format to set the video frame rate.

```php
namespace PHPVideoToolkit;

$output_format = \PHPVideoToolkit\Factory::videoFormat('output');
$output_format->setFrameRate(1);

$video  = Factory::video('BigBuckBunny_320x180.mp4');
$output = $video->extractFrames(null, new Timecode(50)) // if null then the extracted segment starts from the begining of the video
	   			->save('./output/big_buck_bunny_frame_%timecode.jpg', $output_format);
```

The second is to use the $force_frame_rate option of the extractFrames function.

```php
namespace PHPVideoToolkit;

$video  = Factory::video('BigBuckBunny_320x180.mp4');
$output = $video->extractFrames(new Timecode(50), null, 1) // if null then the extracted segment goes from the start timecode to the end of the video
	   			->save('./output/big_buck_bunny_frame_%timecode.jpg');
```

###Extracting Audio or Video Channels from a Video

```php
namespace PHPVideoToolkit;

$video  = Factory::video('BigBuckBunny_320x180.mp4');
$output = $video->extractAudio()->save('./output/big_buck_bunny.mp3');
// $output = $video->extractVideo()->save('./output/big_buck_bunny.mp4');
	   			
```

###Extracting a Segment of an Audio or Video file

The code below extracts a portion of the video at the from 2 minutes 22 seconds to 3 minutes. *Note the different settings for constructing a timecode.*

```php
namespace PHPVideoToolkit;

$video  = Factory::video('BigBuckBunny_320x180.mp4');
$output = $video->extractSegment(new Timecode('00:02:22.0', Timecode::INPUT_FORMAT_TIMECODE), new Timecode(180))
	   			->save('./output/big_buck_bunny.mp4');
```
###Spliting a Audio or Video file into multiple parts

There are multiple ways you can configure the split parameters. If an array is supplied as the first argument. It must be an array of either, all Timecode instances detailing the timecodes at which you wish to split the media, or all integers. If integers are supplied the integers are treated as frame numbers you wish to split at. You can however also split at even intervals by suppling a single integer as the first paramenter. That integer is treated as the number of seconds that you wish to split at. If you have a video that is 3 minutes 30 seconds long and set the split to 60 seconds, you will get 4 videos. The first three will be 60 seconds in length and the last would be 30 seconds in length.

The code below splits a video into multiple of equal length of 45 seconds each. 

```php
namespace PHPVideoToolkit;

$video  = Factory::video('BigBuckBunny_320x180.mp4');
$output = $video->split(45)
	   			->save('./output/big_buck_bunny_%timecode.mp4');
```
###Purging and then adding Meta Data

Unfortunately there is no way using FFmpeg to add meta data without re-encoding the file. There are other tools that can do that.

```php
namespace PHPVideoToolkit;

$video  = Factory::video('BigBuckBunny_320x180.mp4');
$output = $video->purgeMetaData()
				->setMetaData('title', 'Hello World')
	   			->save('./output/big_buck_bunny.mp4');
```
