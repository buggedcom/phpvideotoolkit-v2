#[2.0.0] [25.03.2013]
        - Updated codebase to v2. Main repo is now on github https://github.com/buggedcom/phpvideotoolkit-v2

#[0.1.5] [06.06.2008] 
	- REMOVED dependancy on buffering the exec calls to a file, parsing the file and
	  then unlinking the file. Cuts down considerably of the impact of the class on
	  the server. Thanks Varon. http://www.buggedcom.co.uk/discuss/viewtopic.php?id=10
	- FIXED check for liblamemp3 audio format problem.
	- UPDATED example/index.php to correctly get the current release version, and
	  remove the php notices.
	- UPDATED PHPVideoToolkit::getFileInfo() and PHPVideoToolkit::getFFmpegInfo() so
	  they can now be called statically.
	- UPDATED example06.php to have the media embedded in the example.
	- BUNDLED the Javascript PluginObject package with PHPVideoToolkit. It is an 
	  end-all solution to embedding browser based plugins via javascript. It is 
	  distributed under a BSD License. The full package can be downloaded from:
	  http://sourceforge.net/project/showfiles.php?group_id=223120

#[0.1.4] [10.04.2008] 
	- ADDED phpvideotoolkit.php4.php and renamed the php5 class 
	  phpvideotoolkit.php5.php, however the adapter classes will remain php5 only
	  for the time being. Allow the adapters are php5 it would be very simple for
	  someone to convert them to php4. IF you do let me know and i'll include them
	  in the distribution.
	- FIXED PHP Notice errors, googlecode issue #1, Thanks Rob Coenen.
	- DEPRECIATED setVideoOutputDimensions for setVideoDimensions
	- ADDED PHPVideoToolkit::flvStreamSeek() which acts as a php stream proxy for
	  flash flv files, can also limit bandwidth speed. See example13.php for more info.
	- ADDED example13.php to demo how to use the new flv seeking function flvStreamSeek()
	- DEPRECIATED setAudioFormat for setAudioCodec. setAudioFormat will be removed in 
	  version 0.2.0
	- DEPRECIATED setVideoFormat for setVideoCodec. setVideoFormat will be removed in 
	  version 0.2.0
	- REMOVED dependancy of the ffmpeg-PHP adapter on the getID3 library as is 
	  incompatible with the BSD license. Now integrated with php-reader 
	  http://code.google.com/p/php-reader/ which is licensed under a New BSD license.

#[0.1.3] [04.04.2008] 
	- RENAMED primary class to PHPVideoToolkit to avoid any confusion with 
	  ffmpeg-php
	- THANKS to Istvan Szakacs, and Rob Coenen for providing some valuable feedback, 
	  bug fixes and code contributions.
	- ADDED note to example11.php to warn windows users about getID3's
	  helper files.
	- ADDED example12.php which shows how to manipulate timecodes.
	- CHANGED the behaviour of extractFrames and extractFrame to allow you
	  to specify specific frames and enter different types of timecodes as
	  params using the new $timecode_format argument.
	- ADDED value PHPVideoToolkit::getFFmpegInfo()['ffmpeg-php-support']; Values are
		- 'module' = ffmpeg-php is installed as a php module
		- 'emulated' = ffmpeg-php is supported through the VideoToolkit adapter
		  classes (supplied with this package)
		- false = ffmpeg-php is not supported in any way.
	- ADDED PHPVideoToolkit::hasFFmpegPHPSupport() returns one of the values above,
	  dictating if ffmpeg-php is supported.
	- ADDED PHPVideoToolkit::getFFmpegInfo()['compiler']['vhook-support'] that determines
	  if vhook support has been compiled into the ffmpeg binary.
	- ADDED PHPVideoToolkit::hasVHookSupport() returns a boolean value to determine
	  if vhook support is enabled in the ffmpeg binary.
	- FIXED path include bug in example08.php
	- ADDED frame existence check to extractFrame and extractFrames, thanks to
	  Istvan Szakacs for suggesting the idea.
	- ADDED an extra param to PHPVideoToolkit::setInputFile() and 
	  PHPVideoToolkit::prepareImagesForConversion(), $input_frame_rate. by default it is
	  0 which means no input frame rate is set, if you set it to false for 
	  setInputFile then the frame rate will retrieved, otherwise the input
	  frame rate will be set to whatever integer is set.
	- ADDED frame_count to the duration field of the getFileInfo array. 
	- ADDED check for --enable-liblamemp3 which requires a different codec for
	  setting the audio format as mp3.
	- ADDED width and height check in setVideoOutputDimensions, as apparently
	  the output dimensions have to be even numbers.
	- REMOVED call-by-pass-time-reference dependance from _postProcess()
	- ADDED vhook check to PHPVideoToolkit::addWatermark(), returns false if vhook is not
	  enabled.
	- ADDED PHPVideoToolkit::addGDWatermark() to allow GD watermarking of outputted images.
	- CHANGED the functionality of example04.php to show usage of addGDWatermark
	  if vhooking is not enabled.
	
#[0.1.2] [03.04.2008] 
	- FIXED bug in PHPVideoToolkit::getFileInfo() that in some instances didn't return 
	  the correct information, such as dimensions and frame rate. Thanks to
	  Istvan Szakacs for pointing out the error.
	- CHANGED the way an image sequence is outputted. %d within the naming
	  of the output files is now for internal use only.
	  	%index 		- is the old %d and it also accepts numerical padding.
	  	%timecode 	- is the pattern for hh-mm-ss-fn, where fn is the frame 
					  number.
	- UPDATED example02.php to reflect the changes above.
	- ADDED ffmpeg-php adapters to provide a pure PHP implementation of the
	  ffmpeg-php module.
	- ADDED getID3 to the distribution.
	 	- @link http://getid3.sourceforge.net/
		- @author James Heinrich <info-at-getid3-dot-org>  (et al)
	  	- @license GPL and gCL (getID3 Commerical License).
	- ADDED GifEncoder to the distribution.
	 	@link http://www.phpclasses.org/browse/package/3163.html
	 	@link http://phpclasses.gifs.hu/show.php?src=GIFEncoder.class.php
	  	@author László Zsidi
	  	@license Freeware.
	- ADDED example11.php, example12.php to demonstrate the ffmpeg-php
	  adapters.
	- CHANGED PHPVideoToolkit::getFileInfo()['audio']['frequency'] to
      	PHPVideoToolkit::getFileInfo()['audio']['sample_rate']
	- CHANGED PHPVideoToolkit::getFileInfo()['audio']['format'] to 
	  	PHPVideoToolkit::getFileInfo()['audio']['codec']
	- CHANGED PHPVideoToolkit::getFileInfo()['video']['format'] to 
	  	PHPVideoToolkit::getFileInfo()['video']['codec']
	- ADDED PHPVideoToolkit::getFileInfo()['video']['pixel_format']
	- ADDED PHPVideoToolkit::getFileInfo()['_raw_info'] which is the raw buffer output
	- ADDED PHPVideoToolkit::getFileInfo()['duration']['start'] (re-added)
	- UPDATED PHPVideoToolkit::extractFrame so in some instances it will be less cpu
	  intensive.
	- UPDATED PHPVideoToolkit::_combineCommands so commands can be ordered in the exec
	  string.

#[0.1.1] [29.03.2008] 
	- FIXED bug in the post processing of exporting a series of image frames.
	  With thanks to Rob Coenen.
	- FIXED bug in PHPVideoToolkit::getFileInfo() that returned the incorrect frame
	  rate of videos.
	- CHANGED functionality of PHPVideoToolkit::extractFrame(), to export a specific
	  frame based on the frame number, not just the hours, mins, secs timecode
	- FIXED bug in ffmpeg.example9.php where the gif was incorrectly named.
	- CHANGED functionality of PHPVideoToolkit::getFileInfo(), reorganised the way the 
	  duration data is returned.
	- CHANGED functionality of PHPVideoToolkit::getFileInfo(), so the timecode with
	  frame numbers instead of milliseconds is also returned in the value 
	  duration.timecode.frames.exact, however this value is only available to 
	  video files.
	- REMOVED duration.start from the information returned by 
	  PHPVideoToolkit::getFileInfo()
	- CHANGED PHPVideoToolkit::$image_output_timecode's default value to true/
	- ADDED PHPVideoToolkit::registerPostProcess() to provide a way to automate 
	  callbacks docs so you can hook into post processing of the ffmpeg 
	  output. See function for more info.
	- CHANGED the way PHPVideoToolkit::setFormatToFLV() adds the meta data to the flv.
	  It now uses PHPVideoToolkit::registerPostProcess() to create a callback.
	- FIXED average time mistakes in examples.
	- FIXED overwrite mistakes in examples (it was set to true so the 
	  overwrite mode defaulted to PHPVideoToolkit::OVERWRITE_EXISTING)
	- ADDED internal caching of PHPVideoToolkit::getFileInfo(); so if the data is asked 
	  to be generated more than one in the same script it only gets generated 
	  once.

#[0.1.0] [02.03.2008] 
	- ADDED new constant PHPVideoToolkit::SIZE_SAS. Which stands for Same As Source,  
	  meaning ffmpeg will automatically convert the movie to a whatever format   
	  but preserve the size of the original movie.
	- CORRECTED error/comment spelling mistakes.
	- CHANGED PHPVideoToolkit::getFileInfo(); to use preg_match so it's more reliable, 
	  it also contains more information on the file.
	- ADDED public function setVideoAspectRatio. Sets the video aspect ratio. 
	  Takes one of three constants as an argument. PHPVideoToolkit::RATIO_STANDARD, 
	  PHPVideoToolkit::RATIO_WIDE, PHPVideoToolkit::RATIO_CINEMATIC
	- ADDED public function setVideoBitRate. Sets the video bitrate.
	- ADDED public function setVideoFormat. Sets a video codec. It should not 
	  be confused with PHPVideoToolkit::setFormat. It provides slightly different  
	  advanced functionality, most simple usage can just use PHPVideoToolkit::setFormat
	- ADDED public function setAudioFormat. Sets an audio codec.
	- ADDED public function setConstantQuality. Sets a constant encoding 
	  quality.
	- ADDED public function getFFmpegInfo. Gets the available data from ffmpeg 
	  and stores the output in PHPVideoToolkit::$ffmpeg_info (below).
	- ADDED PHPVideoToolkit::$ffmpeg_info static var to hold the output of 
	  PHPVideoToolkit::getFFmpegInfo();
	- ADDED public function getLastProcessTime and getProcessTime to retrieve 
	  the processing times of the ffmpeg calls.
	- ADDED adapter classes to provide simple functionality for ffmpeg newbies 
	  / quick solutions. Each option set can be supplied in the second 
	  argument as part of an array.
			VideoTo::PSP(); 	- Converts video into the PSP mp4 video.
			VideoTo::iPod(); 	- Converts video into the iPod mp4 video.
			VideoTo::FLV(); 	- Converts video into the Flash video (flv).
			VideoTo::Gif(); 	- Converts video into the animated gif.
								  (experimental as quality is poor)
	- CHANGED the way the processing works. The file is processed to the 
	  temp directory and is then checked for consistency before moving to 
	  the output directory.
	- CHANGED the return values of PHPVideoToolkit::execute(); It no longer returns 
	  just true or false. See class docs for more info.
	- CHANGED the third argument in PHPVideoToolkit::setOutput() from $overwrite to 
	  $overwrite_mode. Instead of a boolean value, it now takes one of three
	  constants
			ffmegp::OVERWRITE_FAIL		- means that if a conflict exists the 
									  	  process will result in and error.
			ffmegp::OVERWRITE_PRESERVE	- means that if a conflict exists the 
									  	  process will preserve the existing
									 	  file and report with 
										  PHPVideoToolkit::RESULT_OK_BUT_UNWRITABLE.
			ffmegp::OVERWRITE_EXISTING	- means that if a conflict exists the 
									  	  process will overwrite any existing 
										  file with the new file.
			ffmegp::OVERWRITE_UNIQUE	- means that every filename is 
										  prepended with a unique hash to
										  preserve the  existing filesystem.
	- MOVED error messages into a class variable for easier 
	  translation/changes.
	- CHANGED moveLog functionality to use rename instead of copy and unlink.

#[0.0.9] [12.02.2008] 
	- Added new definition FFMPEG_MENCODER_BINARY to point to the mencoder 
	  binary.
	- Changed the behavior of setVideoOutputDimensions. it now accepts class
	  constants as preset sizes.
	- Added public function adjustVolume. Sets the audio volume.
	- Added public function extractAudio. Extracts audio from video.
	- Added public function disableAudio. Disables audio encoding.
	- Added public function getFileInfo. Access information about the media 
	  file.
	  Without using ffmpeg-php as it queries the binary directly.
	- Added 2 arguments to excecute. 
		argument 1 - $multi_pass_encode (boolean). Determines if ffmpeg should 
					 multipass encode. Can result in a better quality encode.
					 default false
		argument 2 - $log (boolean). Determines if the output of the query to 
					 the ffmpeg binary is logged. Note, any log file created 
					 is destroyed unless moved with PHPVideoToolkit::moveLog upon
					 destruct of the ffmpeg instance or on PHPVideoToolkit::reset.
					 default false
	- Added public function moveLog. Moves a log file.
	- Added public function readLog. Reads a log file and returns the data.
	- Changed external format definitions to internal class constants.
	- Changed external use high quality join flag to internal class constant.
	- Fixed bug in setFormat error message.
	- Fixed bug in execute.

#[0.0.8] [07.08.2007] 
	- Added public functions secondsToTimecode & timecodeToSeconds. Translates
	  seconds into a timecode and visa versa.
	  ie. 82 => 00:01:22 & 00:01:22 => 82
	- Added public var image_output_timecode. Determines if any outputted
	  frames are re-stamped with the frames timecode if true.
	- Fixed bug in setOutput.

#[0.0.7] [01.08.2007] 
    - Added FFMPEG_FORMAT_Y4MP format (yuv4mpegpipe).
    - Added extra information to install.txt
    - Added public function hasCommand.
	- Added public functions addVideo, addVideos
    - Changed the behavior of setInputFile to take into account the addVideos
	  function. It now can take multiple input files for joining as well as
	  the high quality join flag 'FFMPEG_USE_HQ_JOIN'.
    - Changed the behavior of setOutput. If the $output_name has a common 
      image extension then and no %d is found then an error is raised.
    - Changed all booleans from upper to lower case.
    
#[0.0.5] [12.03.2007] 
    - Added FFMPEG_FORMAT_JPG format (mjpeg). Thanks Matthias.
    - Changed the behavior of extractFrames. It now accepts a boolean FALSE
      argument for $extract_end_timecode. 
      If it is given then all frames are exported from the timecode specified
      by $extract_begin_timecode. Thanks Matthias.
	- Added extra definition 'FFMPEG_WATERMARK_VHOOK' for the path to the
	  watermark vhook.
    - Added watermark support for both frames exports and videos. (Note: this
	  makes specific useage of vhook. If your ffmpeg binary has not been
	  compiled with --enable-vhook then this will not work.
    
#[0.0.1] [02.03.2007] 
    - Initial version released
