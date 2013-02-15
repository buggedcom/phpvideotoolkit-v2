<?php
	
	/**
	 * This file is part of the PHP Video Toolkit v2 package.
	 *
	 * @author Oliver Lillie (aka buggedcom) <publicmail@buggedcom.co.uk>
	 * @license Dual licensed under MIT and GPLv2
	 * @copyright Copyright (c) 2008 Oliver Lillie <http://www.buggedcom.co.uk>
	 * @package PHPVideoToolkit V2
	 * @version 2.0.0.a
	 * @uses ffmpeg http://ffmpeg.sourceforge.net/
	 */
	 
	 namespace PHPVideoToolkit;
	 
	/**
	 * This class provides generic data parsing for the output from FFmpeg.
	 *
	 * @access public
	 * @author Oliver Lillie
	 * @author Jorrit Schippers
	 * @package default
	 */
	class AnimatedGifTranscoderPhp extends AnimatedGifTranscoderAbstract
	{
		// // 				convert the images
		// 				$gif = new GIFEncoder($files, $delays, $this->_loop_count, 2, 0, 0, 0, 0, 'url');
		// 				$gif_data = $gif->GetAnimation();
		// 				if(!$gif_data)
		// 				{
		// 					return false;
		// 				}
		// // 				write the gif
		// 				if (!$handle = fopen($this->_output_file_path, 'w'))
		// 				{
		// 					return false;
		// 				}
		// 				if (fwrite($handle, $gif_data) === false)
		// 				{
		// 					return false;
		// 				}
		// 				fclose($handle);
	}
