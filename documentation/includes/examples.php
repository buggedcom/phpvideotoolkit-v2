<?php
	
	if(empty($examples) === true)
	{
		
?>

	<div class="span9 pull-right">
	
		<p>There are no examples for this documentation.</p>

	</div>

<?php
		
	}
	else
	{
		foreach ($examples as $example)
		{
			$example_data = file_get_contents($example['path']);
			$example_data = str_replace('	', '  ', $example_data);
			$example_data = str_replace('$example_video_path', '\''.addslashes($example_video_path).'\'', $example_data);
			$example_data = str_replace('$example_audio_path', '\''.addslashes($example_audio_path).'\'', $example_data);
			$example_data = str_replace(BASE, './', $example_data);

?>

	<div class="span9 pull-right">
		<h4><?php echo HTML($example['name']); ?></h4>
		<p><?php echo nl2br(HTML($example['description'])); ?></p>
		<p><?php echo HTML($example['path']); ?></p>
		<pre class="prettyprint"><code><?php echo HTML($example_data); ?></code></pre>
	</div>

<?php
	
		}
	}
	
