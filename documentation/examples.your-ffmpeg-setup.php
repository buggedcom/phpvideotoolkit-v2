<?php
    
    require_once './includes/header.php';
    
?>

        <div class="span9 pull-right">
          <h1>Examples</h1>
          <h2>Your FFmpeg Setup</h2>
          <p>FFmpeg is an incredible tool, but you need to know how your version of FFmpeg is configured and what you can do with it before you start using it. Typically each copy of FFmpeg is configured in different ways. Certain videos can't be encoded but they can be decoded and then encdoed into a different format.</p>
        </div><!--/span-->
        
        <div class="span9 pull-right">
          <h4>Do you have FFmpeg installed?</h4>
          
<?php
    
	$ffmpeg_parser = null;
    $is_available = false;
    $ffmpeg_version = false;
    if(PROGRAM_PATH !== null)
    {
        $ffmpeg_parser = new \PHPVideoToolkit\FfmpegParser($config);
        $is_available = $ffmpeg_parser->isAvailable();
        $ffmpeg_version = $ffmpeg_parser->getVersion();    
    }   
    
?>
          
          <p><?php if($is_available === true): ?>Yes, you have FFmpeg installed and available. Version: <?php echo $ffmpeg_version['version'] === null ? 'unknown' : HTML($ffmpeg_version['version']); ?> (build <?php echo $ffmpeg_version['build'] === null ? 'unknown' : HTML($ffmpeg_version['build']); ?>)<?php else: ?>No, it does not appear as if you have FFmpeg installed.<?php endif ?></p>
          <pre class="prettyprint"><code>&lt;?php
              
  $config = new \PHPVideoToolkit\Config(array(
	'temp_directory' => '<?php echo addslashes(HTML(TEMP_PATH)); ?>', 
	'ffmpeg' => '<?php echo addslashes(HTML(FFMPEG_PROGRAM)); ?>', 
	'ffprobe' => '<?php echo addslashes(HTML(FFPROBE_PROGRAM)); ?>',
  ));
              
  $parser = new \PHPVideoToolkit\FfmpegParser($config);
  $is_available = $ffmpeg->isAvailable(); // returns boolean
  $ffmpeg_version = $ffmpeg->getVersion(); // outputs something like - array('version'=>1.0, 'build'=>null)
          
</code></pre>
          
        </div><!--/span-->
		
		<hr />
        
<?php
    
    if($is_available === false)
    {
        
?>      

        <div class="span9 pull-right">
            <div class="alert">
                <strong>FFmpeg does not appear to be available.</strong> 
                <p>We can't show you the other examples in this page because they require that you have access to FFmpeg. If you have already set the configuration options in <?php echo dirname(__FILE__); ?>/configuration.php please make sure they are correct and then reload the page.</p>
            </div>
        </div>

<?php

    }
    else
    {
        
?>      
        <div class="span9 pull-right">
          <h4>Do you have FFmpeg-PHP support?</h4>
		  
              <div class="alert">
                  <strong>FFmpeg-PHP is no longer maintained</strong> 
                  <p>FFmpeg-PHP is no longer being maintained by the authors. There are several forks of the project, which you can find with a quick search on Google, however we recommend that you use PHPVideoToolkits emulation of FFmpeg-PHP instead.</p>
              </div>
          
		  <p>To learn more about what FFmpeg-PHP is please read the <a href="http://ffmpeg-php.sourceforge.net/">FFmpeg-PHP documentation</a>.</p>
          
<?php
    
		$has_ffmpeg_support = $ffmpeg_parser->hasFfmpegPhpSupport();
    
?>
          
          <p><?php if($has_ffmpeg_support !== false): ?>Yes, you have FFmpeg-PHP support. FFmpeg-PHP is provided through <?php if($has_ffmpeg_support === 'module'): ?>the PHP module<?php else: ?>PHPVideoToolkit emulation<?php endif ?><?php else: ?>No, it does not appear that you have any support for PHPVideoToolkit<?php endif ?>.</p>
          <pre class="prettyprint"><code>&lt;?php
              
  $config = new \PHPVideoToolkit\Config(array(
	'temp_directory' => '<?php echo addslashes(HTML(TEMP_PATH)); ?>', 
	'ffmpeg' => '<?php echo addslashes(HTML(FFMPEG_PROGRAM)); ?>', 
	'ffprobe' => '<?php echo addslashes(HTML(FFPROBE_PROGRAM)); ?>',
  ));
              
  $ffmpeg = new \PHPVideoToolkit\FfmpegParser($config);
  $has_ffmpeg_support = $ffmpeg->hasFfmpegPhpSupport(); // returns either "module", "emulated" or false.
          
</code></pre>
          
        </div><!--/span-->
        
        <div class="span9 pull-right">
          <h4>Basic information about FFmpeg</h4>
          
<?php
    
        $basic_ffmpeg_information = $ffmpeg_parser->getFfmpegData();
        
?>
          
          <pre class="prettyprint"><code>&lt;?php
              
  $config = new \PHPVideoToolkit\Config(array(
    'temp_directory' => '<?php echo addslashes(HTML(TEMP_PATH)); ?>', 
    'ffmpeg' => '<?php echo addslashes(HTML(FFMPEG_PROGRAM)); ?>', 
    'ffprobe' => '<?php echo addslashes(HTML(FFPROBE_PROGRAM)); ?>',
  ));
              
  $ffmpeg = new \PHPVideoToolkit\FfmpegParser($config);
  $basic_ffmpeg_information = $ffmpeg->getFfmpegData(); // returns an array of data.
          
</code></pre>
          
            <p>The compiler used to build FFmpeg was <em><?php echo HTML($basic_ffmpeg_information['compiler']['gcc']); ?></em> and FFmpeg was compiled on <?php echo date('jS F, Y \a\t H:i', $basic_ffmpeg_information['compiler']['build_date']); ?>. The following list contains the configuration flags that were used when FFmpeg was compiled.</p>
          
            <table class="table table-striped table-condensed table-bordered">
                <tbody>
                    
                    <?php foreach($basic_ffmpeg_information['binary']['configuration'] as $flag): ?>
                        
                        <tr>
                            <td><?php echo HTML($flag); ?></td>
                        </tr>
                        
                    <?php endforeach ?>
                    
                </tbody>
            </table>
            
        </div><!--/span-->
        
        <div class="span9 pull-right">
          <h4>Which commands does your FFmpeg support?</h4>
          
<?php
    
	    $ffmpeg_commands = $ffmpeg_parser->getCommands(true);
		ksort($ffmpeg_commands);
    
?>
          
            <pre class="prettyprint"><code>&lt;?php
              
  $config = new \PHPVideoToolkit\Config(array(
	'temp_directory' => '<?php echo addslashes(HTML(TEMP_PATH)); ?>', 
	'ffmpeg' => '<?php echo addslashes(HTML(FFMPEG_PROGRAM)); ?>', 
	'ffprobe' => '<?php echo addslashes(HTML(FFPROBE_PROGRAM)); ?>',
  ));
              
  $ffmpeg = new \PHPVideoToolkit\FfmpegParser($config);
  $basic_ffmpeg_information = $ffmpeg->getCommands(); // returns an array of data.
          
</code></pre>
          
            <p>The following list contains the commands available to FFmpeg and their related explanations. Any rows that are highlighted in red have been removed, yellow are depreciated and should no longer be used.</p>
          
            <table class="table table-striped table-condensed table-bordered">
                <thead>
                    <th>Command</th>
                    <th>Description</th>
                    <th>Data Type</th>
                    <th>Arguments (if any)</th>
                    <th>Depreciated/Removed Status</th>
                </thead>
                <tbody>
                    
                    <?php foreach($ffmpeg_commands as $command=>$info): ?>
                        
                        <tr<?php if($info['status'] === 'deprecated'): ?> class="warning"<?php elseif($info['status'] === 'removed'): ?> class="error"<?php endif ?>>
                            <td>-<?php echo HTML($command); ?></td>
                            <td><?php echo HTML($info['description']); ?></td>
                            <td><?php echo HTML($info['datatype']); ?></td>
                            <td><?php echo HTML(implode(' ', $info['arguments'])); ?></td>
                            <td><?php echo $info['status'] ? HTML($info['status']) : ''; ?></td>
                        </tr>
                        
                    <?php endforeach ?>
                    
                </tbody>
            </table>
            
        </div><!--/span-->
        
        <div class="span9 pull-right">
          <h4>What formats can you decode/encode?</h4>
		  
		  <p>To understand more about formats please read the <a href="http://ffmpeg.org/ffmpeg-formats.html">FFmpeg documentation</a>.</p>
          
<?php
    
        $ffmpeg_formats = $ffmpeg_parser->getFormats();
    // \PHPVideoToolkit\Trace::vars($ffmpeg_formats);exit;
    
?>
          
            <pre class="prettyprint"><code>&lt;?php
              
  $config = new \PHPVideoToolkit\Config(array(
	'temp_directory' => '<?php echo addslashes(HTML(TEMP_PATH)); ?>', 
	'ffmpeg' => '<?php echo addslashes(HTML(FFMPEG_PROGRAM)); ?>', 
	'ffprobe' => '<?php echo addslashes(HTML(FFPROBE_PROGRAM)); ?>',
   ));

  $ffmpeg = new \PHPVideoToolkit\FfmpegParser($config);
  $ffmpeg_formats = $ffmpeg->getFormats(); // returns an array of data.
          
</code></pre>
          
            <p>The following list are the formats available to FFmpeg and whether or not they can be muxed and demuxed. Rows highlighted in green can both be muxed and demuxed.</p>
          
            <table class="table table-striped table-condensed table-bordered">
                <thead>
                    <th>Full Name</th>
                    <th>Identifier</th>
                    <th>Can be Decoded (Demuxed)</th>
                    <th>Can be Encoded (Muxed)</th>
                    <th>Recognised Extensions</th>
                </thead>
                <tbody>
                    
                    <?php foreach($ffmpeg_formats as $code=>$info): ?>
                        
                        <tr<?php if($info['demux'] === true && $info['mux'] === true): ?> class="success"<?php endif ?>>
                            <td><?php echo HTML($info['fullname']); ?></td>
                            <td><?php echo HTML($code); ?></td>
                            <td><?php echo $info['demux'] === true ? 'yes' : ''; ?></td>
                            <td><?php echo $info['mux'] === true ? 'yes' : ''; ?></td>
                            <td><?php echo $info['extensions'] !== false ? implode(', ', $info['extensions']) : ''; ?></td>
                        </tr>
                        
                    <?php endforeach ?>
                    
                </tbody>
            </table>
            
        </div><!--/span-->
        
        
        <div class="span9 pull-right">
          <h4>Which codecs are available to you?</h4>
		  
		  <p>To understand more about codecs please read the <a href="http://ffmpeg.org/ffmpeg-codecs.html">FFmpeg documentation</a>.</p>
          
<?php
    
        $ffmpeg_codecs = $ffmpeg_parser->getCodecs();
    
?>
          
        <pre class="prettyprint"><code>&lt;?php
              
  $config = new \PHPVideoToolkit\Config(array(
	'temp_directory' => '<?php echo addslashes(HTML(TEMP_PATH)); ?>', 
	'ffmpeg' => '<?php echo addslashes(HTML(FFMPEG_PROGRAM)); ?>', 
	'ffprobe' => '<?php echo addslashes(HTML(FFPROBE_PROGRAM)); ?>',
  ));
              
  $ffmpeg = new \PHPVideoToolkit\FfmpegParser($config);
    
  // a component can be specified to return specific codec information.
  // if left as null, all component information will be returned.
  $component = null;
  // $component = 'audio';
  // $component = 'video';
  // $component = 'subtitle';
  $ffmpeg_codecs = $ffmpeg->getCodecs($component); // returns an array of data.
          
</code></pre>
          
            <p>The following lists are the different types of codecs available to FFmpeg and whether or not they can be decoded and encoded. Rows highlighted in green can both be decoded and encoded.</p>
          
            <?php foreach($ffmpeg_codecs as $type=>$codecs): ?>
                
            <h5><?php echo HTML(ucfirst($type)); ?> Codecs</h5>
                        
            <table class="table table-striped table-condensed table-bordered">
                <thead>
                    <th>Full Name</th>
                    <th>Identifier</th>
                    <th>Can be Decoded</th>
                    <th>Can be Encoded</th>
                </thead>
                <tbody>
                    
                    <?php foreach($codecs as $code=>$info): ?>
                        
                        <tr<?php if($info['decode'] === true && $info['encode'] === true): ?> class="success"<?php endif ?>>
                            <td><?php echo HTML($info['fullname']); ?></td>
                            <td><?php echo HTML($code); ?></td>
                            <td><?php echo $info['decode'] === true ? 'yes' : ''; ?></td>
                            <td><?php echo $info['encode'] === true ? 'yes' : ''; ?></td>
                        </tr>
                        
                    <?php endforeach ?>
                    
                </tbody>
            </table>
            
            <?php endforeach ?>
        
        </div><!--/span-->
        
        <div class="span9 pull-right">
          <h4>Available bitstream filters</h4>
		  
		  <p>To understand more about bitstream filters please read the <a href="http://ffmpeg.org/ffmpeg-bitstream-filters.html">FFmpeg documentation</a>.</p>
          
<?php
    
		$ffmpeg_bitstream_filters = $ffmpeg_parser->getBitstreamFilters();
		sort($ffmpeg_bitstream_filters);
    
	//\PHPVideoToolkit\Trace::vars($ffmpeg_bitstream_filters);
    
?>
          
          <pre class="prettyprint"><code>&lt;?php
              
  $config = new \PHPVideoToolkit\Config(array(
    'temp_directory' => '<?php echo addslashes(HTML(TEMP_PATH)); ?>', 
    'ffmpeg' => '<?php echo addslashes(HTML(FFMPEG_PROGRAM)); ?>', 
    'ffprobe' => '<?php echo addslashes(HTML(FFPROBE_PROGRAM)); ?>',
  ));
              
  $ffmpeg = new \PHPVideoToolkit\FfmpegParser($config);
  $ffmpeg_bitstream_filters = $ffmpeg->getBitstreamFilters(); // returns an array of data.
          
</code></pre>
          
          <p>The following list are the bitstream filters available to your copy of FFmpeg.</p>
          
          <table class="table table-striped table-condensed table-bordered">
              <tbody>
                    
                  <?php foreach($ffmpeg_bitstream_filters as $filter): ?>
                        
                      <tr>
                          <td><?php echo HTML($filter); ?></td>
                      </tr>
                        
                  <?php endforeach ?>
                    
              </tbody>
          </table>
            
        </div><!--/span-->
        
        <div class="span9 pull-right">
          <h4>Available Filters</h4>
		  
		  <p>To understand more about filters please read the <a href="http://ffmpeg.org/ffmpeg-filters.html">FFmpeg documentation</a>.</p>
          
<?php
    
		$ffmpeg_filters = $ffmpeg_parser->getFilters(false);
		ksort($ffmpeg_filters);
    
	//\PHPVideoToolkit\Trace::vars($ffmpeg_filters);exit;
    
?>
          
          <pre class="prettyprint"><code>&lt;?php
              
  $config = new \PHPVideoToolkit\Config(array(
    'temp_directory' => '<?php echo addslashes(HTML(TEMP_PATH)); ?>', 
    'ffmpeg' => '<?php echo addslashes(HTML(FFMPEG_PROGRAM)); ?>', 
    'ffprobe' => '<?php echo addslashes(HTML(FFPROBE_PROGRAM)); ?>',
  ));
              
  $ffmpeg = new \PHPVideoToolkit\FfmpegParser($config);
  
  // instead of returning all the data about each filter, you can limit it to 
  // just the filter names instead.
  $just_filter_names = false;
  // $just_filter_names = true;
  $ffmpeg_filters = $ffmpeg->getFilters(); // returns an array of data.
          
</code></pre>
          
          <p>The table below lists the available filters to FFmpeg. It is quite important to understand what the direction means. Please read the <a href="http://ffmpeg.org/ffmpeg-filters.html">FFmpeg documentation on Filters</a> for further help.</p>
          
          <table class="table table-striped table-condensed table-bordered">
              <thead>
                  <th>Identifier</th>
                  <th>Description</th>
                  <th>Direction</th>
              </thead>
              <tbody>
                    
                  <?php foreach($ffmpeg_filters as $code=>$info): ?>
                        
                      <tr>
                          <td><?php echo HTML($code); ?></td>
                          <td><?php echo HTML($info['description']); ?></td>
                          <td><?php echo HTML($info['from']); ?> =&gt; <?php echo HTML($info['to']); ?></td>
                      </tr>
                        
                  <?php endforeach ?>
                    
              </tbody>
          </table>
            
        </div><!--/span-->
        
        <div class="span9 pull-right">
          <h4>Protocols that FFmpeg supports</h4>
		 
		  <p>To understand more about the protocols that FFmpeg can use please read the <a href="http://ffmpeg.org/ffmpeg-protocols.html">FFmpeg documentation</a>.</p>
          
<?php
    
		$ffmpeg_protocols = $ffmpeg_parser->getProtocols();
		ksort($ffmpeg_protocols);
    
	//\PHPVideoToolkit\Trace::vars($ffmpeg_protocols);exit;
    
?>
          
          <pre class="prettyprint"><code>&lt;?php
              
  $config = new \PHPVideoToolkit\Config(array(
	'temp_directory' => '<?php echo addslashes(HTML(TEMP_PATH)); ?>', 
	'ffmpeg' => '<?php echo addslashes(HTML(FFMPEG_PROGRAM)); ?>', 
	'ffprobe' => '<?php echo addslashes(HTML(FFPROBE_PROGRAM)); ?>',
  ));
              
  $ffmpeg = new \PHPVideoToolkit\FfmpegParser($config);
  $ffmpeg_protocols = $ffmpeg->getProtocols(); // returns an array of data.
          
</code></pre>
          
          <p>The table below lists the available protocols that FFmpeg can utilise. Rows highlighted in green can both be used for input and output.</p>
          
          <table class="table table-striped table-condensed table-bordered">
              <thead>
                  <th>Protocol</th>
                  <th>Input</th>
                  <th>Output</th>
              </thead>
              <tbody>
                    
                  <?php foreach($ffmpeg_protocols as $code=>$info): ?>
                        
                      <tr<?php if($info['input'] === true && $info['output'] === true): ?> class="success"<?php endif ?>>
                          <td><?php echo HTML($code); ?></td>
                          <td><?php echo $info['input'] === true ? 'yes' : ''; ?></td>
                          <td><?php echo $info['output'] === true ? 'yes' : ''; ?></td>
                      </tr>
                        
                  <?php endforeach ?>
                    
              </tbody>
          </table>
            
        </div><!--/span-->
        
        <div class="span9 pull-right">
          <h4>Pixel formats that FFmpeg supports</h4>
		 
		  <p>To understand more about the pixel formats that FFmpeg supports please read the <a href="http://ffmpeg.org/ffmpeg.html">FFmpeg documentation</a>.</p>
          
<?php
    
		$ffmpeg_pixel_formats = $ffmpeg_parser->getPixelFormats();
		ksort($ffmpeg_pixel_formats);
    
	//\PHPVideoToolkit\Trace::vars($ffmpeg_pixel_formats);exit;
    
?>
          
          <pre class="prettyprint"><code>&lt;?php
              
  $config = new \PHPVideoToolkit\Config(array(
	'temp_directory' => '<?php echo addslashes(HTML(TEMP_PATH)); ?>', 
	'ffmpeg' => '<?php echo addslashes(HTML(FFMPEG_PROGRAM)); ?>', 
	'ffprobe' => '<?php echo addslashes(HTML(FFPROBE_PROGRAM)); ?>',
  ));
              
  $ffmpeg = new \PHPVideoToolkit\FfmpegParser($config);
  $ffmpeg_pixel_formats = $ffmpeg->getPixelFormats(); // returns an array of data.
          
</code></pre>
          
          <p>The table below lists the available pixel formats that FFmpeg can utilise. Rows highlighted in green can both be used for encoding and decoding.</p>
          
          <table class="table table-striped table-condensed table-bordered">
              <thead>
                  <th>Identifier</th>
                  <th>Can be Encoded</th>
                  <th>Can be Decoded</th>
                  <th>No. of Channels</th>
                  <th>Bits per Pixel</th>
                  <th>Hardware Accelerated</th>
                  <th>Paletted Format</th>
                  <th>Bitstream Format</th>
                  <th>Supports Alpha</th>
              </thead>
              <tbody>
                    
                  <?php foreach($ffmpeg_pixel_formats as $code=>$info): ?>
                        
                      <tr<?php if($info['encode'] === true && $info['decode'] === true): ?> class="success"<?php endif ?>>
                          <td><?php echo HTML($code); ?></td>
                          <td><?php echo $info['encode'] === true ? 'yes' : ''; ?></td>
                          <td><?php echo $info['decode'] === true ? 'yes' : ''; ?></td>
                          <td><?php echo HTML($info['components']); ?></td>
                          <td><?php echo HTML($info['bpp']); ?></td>
                          <td><?php echo $info['hardware_accelerated'] === null ? 'n/a' : ($info['hardware_accelerated'] === true ? 'yes' : ''); ?></td>
                          <td><?php echo $info['paletted_format'] === null ? 'n/a' : ($info['paletted_format'] === true ? 'yes' : ''); ?></td>
                          <td><?php echo $info['bitstream_format'] === null ? 'n/a' : ($info['bitstream_format'] === true ? 'yes' : ''); ?></td>
                          <td><?php echo $info['alpha'] === null ? 'n/a' : ($info['alpha'] === true ? 'yes' : ''); ?></td>
                      </tr>
                        
                  <?php endforeach ?>
                    
              </tbody>
          </table>
            
        </div><!--/span-->
        
<?php
        
    }
    
?>
        
<?php
    
    require_once './includes/comments.php';
    require_once './includes/footer.php';
    