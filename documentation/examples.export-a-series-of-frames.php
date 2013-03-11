<?php
	
	require_once './includes/header.php';
	
?>

        <div class="span9">
		  <h1>Examples</h1>
  		  <h2>Export a Series of Frames</h2>
          <p>Exporting a series of frames from a video file is very simple. The example below shows you how.</p>
        </div><!--/span-->
		
<?php
	
	$examples = array(
		array(
			'path' => BASE.'examples/extract-frames.example1.php',
			'name' => '',
			'description' => '',
		),
	);
	
	require_once './includes/examples.php';
	
	require_once './includes/comments.php';
	
	require_once './includes/footer.php';
	
