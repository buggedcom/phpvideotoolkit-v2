<?php
	
	require_once './includes/header.php';
	
?>

        <div class="span9">
		  <h1>Examples</h1>
  		  <h2>Export a Single Frame</h2>
          <p>PHPVideoToolkit makes it incredible simple to extract a single frame from a video. Below several examples exist to show you how.</p>
        </div><!--/span-->
		
<?php
	
	$examples = array(
		array(
			'path' => BASE.'examples/extract-frame.example1.php',
			'name' => '',
			'description' => '',
		),
		array(
			'path' => BASE.'examples/extract-frame.example2.php',
			'name' => '',
			'description' => '',
		),
	);
	
	require_once './includes/examples.php';
	
	require_once './includes/comments.php';
	
	require_once './includes/footer.php';
	