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
	 * @access public
	 * @author Oliver Lillie
	 * @package default
	 */
	class Formats
	{
		/**
		 * This is a list of formats to valid extensions.
		 * Taken directly from the libavformat files inside ffmpeg.
		 * This list may not be complete and will need updating from time to time.
		 * Last updated 2013-03-01
		 *
		 * @access public
		 * @author Oliver Lillie
		 */
		static $format_to_extensions = array(
			'a64' => array('a64', 'A64'),
			'aac' => array('aac'),
			'ace' => array('ac3', 'eac3'),
			'adts' => array('aac,adts'),
			'adx' => array('adx'),
			'aea' => array('aea'),
			'aiff' => array('aif', 'aiff', 'afc', 'aifc'),
			'amr' => array('amr'),
			'ape' => array('ape', 'apl', 'mac'),
			'aqtitle' => array('aqt'),
			'asf' => array('asf', 'wmv', 'wma'),
			'asf_stream' => array('asf', 'wmv', 'wma'),
			'ass' => array('ass', 'ssa'),
			'ast' => array('ast'),
			'au' => array('au'),
			'avi' => array('avi'),
			'avisynth' => array('avs'),
			'avs' => array('avs'),
			'avr' => array('avr'),
			'bin' => array('bin'),
			'adf' => array('adf'),
			'idf' => array('idf'),
			'bit' => array('bit'),
			'bmv' => array('bmv'),
			'brstm' => array('brstm'),
			'caf' => array('caf'),
			'cdg' => array('cdg'),
			'cdxl' => array('cdxl', 'xl'),
			'daud' => array('302', 'daud'),
			'dts' => array('dts'),
			'dtshd' => array('dtshd'),
			'dv' => array('dv', 'dif'),
			'ea_cdata' => array('cdata'),
			'epaf' => array('pgaf', 'fap'),
			'ffm' => array('ffm'),
			'ffmetadata' => array('ffmeta'),
			'filmstrip' => array('flm'),
			'flac' => array('flac'),
			'flv' => array('flv'),
			'g722' => array('g722', '722'),
			'g723_1' => array('tco', 'rco', 'g723_1'),
			'g729' => array('g729'),
			'gif' => array('gif'),
			'gsm' => array('gsm'),
			'gxf' => array('gxf'),
			'hls' => array('m3u8'),
			'ico' => array('ico'),
			'roq' => array('roq'),
			'ilbc' => array('lbc'),
			'image2' => array('bmp', 'dpx', 'jls', 'jpeg', 'jpg', 'ljpg', 'pam', 'pbm', 'pcx', 'pgm', 'pgmyuv', 'png', 'ppm', 'sgi', 'tga', 'tif', 'tiff', 'jp2', 'j2c', 'xwd', 'sun', 'ras', 'rs', 'im1', 'im8', 'im24', 'sunras', 'xbm', 'xface'),
			'ingenient' => array('cgi'),
			'ircam' => array('sf', 'ircam'),
			'ivf' => array('ivf'),
			'jacosub' => array('jss', 'js'),
			'latm' => array('latm', 'loas'),
			'libmodplug' => array('669', 'abc', 'amf', 'ams', 'dbm', 'dmf', 'dsm', 'far', 'it', 'mdl', 'med', 'mid', 'mod', 'mt2', 'mtm', 'okt', 'psm', 'ptm', 's3m', 'stm', 'ult', 'umx', 'xm', 'itgz', 'itr', 'itz', 'mdgz', 'mdr', 'mdz', 's3gz', 's3r', 's3z', 'xmgz', 'xmr', 'xmz'),
			'libnut' => array('nut'),
			'lvf' => array('lvf'),
			'matroska' => array('mkv', 'mka'),
			'webm' => array('webm'),
			'microdvd' => array('sub'),
			'mmf' => array('mmf'),
			'mov' => array('mov'),
			'3gp' => array('3gp'),
			'mp4' => array('mp4'),
			'psp' => array('mp4', 'psp'),
			'3g2' => array('3g2'),
			'ipod' => array('m4v', 'm4a'),
			'ismv' => array('ismv', 'isma'),
			'f4v' => array('f4v'),
			'mp3' => array('mp2', 'mp3', 'm2a'),
			'mp2' => array('mp2', 'm2a', 'mp3'),
			'mpc' => array('mpc'),
			'vobsub' => array('idx'),
			'mpeg' => array('mpg', 'mpeg'),
			'vob' => array('vob'),
			'svcd' => array('vob'),
			'dvd' => array('dvd'),
			'mpegts' => array('ts', 'm2t', 'm2ts', 'mts'),
			'mpjpeg' => array('mjpg'),
			'mpl2' => array('txt', 'mpl2'),
			'mpsub' => array('sub'),
			'mvi' => array('mvi'),
			'mxf' => array('mxf'),
			'mxg' => array('mxg'),
			'nc' => array('v'),
			'nistsphere' => array('nist', 'sph'),
			'nut' => array('nut'),
			'ogg' => array('ogg', 'ogv', 'spx', 'opus'),
			'oma' => array('oma', 'omg', 'aa3'),
			'oma' => array('oma'),
			's16be' => array('sw'),
			's16le' => array('sw'),
			's8' => array('sb'),
			'u16be' => array('uw'),
			'u16le' => array('uw'),
			'u8' => array('ub'),
			'alaw' => array('al'),
			'mulaw' => array('ul'),
			'pjs' => array('pjs'),
			'pvf' => array('pvf'),
			'latm' => array('latm'),
			'mlp' => array('mlp'),
			'truehd' => array('thd'),
			'shn' => array('shn'),
			'vc1' => array('vc1'),
			'ac3' => array('ac3'),
			'adx' => array('adx'),
			'cavsvideo' => array('cavs'),
			'dirac' => array('drc'),
			'dnxhd' => array('dnxhd'),
			'dts' => array('dts'),
			'eac3' => array('eac3'),
			'g722' => array('g722'),
			'g723_1' => array('tco', 'rco'),
			'h261' => array('h261'),
			'h263' => array('h263'),
			'h264' => array('h264'),
			'm4v' => array('m4v'),
			'mjpeg' => array('mjpg', 'mjpeg'),
			'mlp' => array('mlp'),
			'mpeg1video' => array('mpg', 'mpeg', 'm1v'),
			'mpeg2video' => array('m2v'),
			'rawvideo' => array('yuv', 'rgb'),
			'truehd' => array('thd'),
			'rawvideo' => array('yuv', 'cif', 'qcif', 'rgb'),
			'realtext' => array('rt'),
			'rm' => array('rm', 'ra'),
			'rso' => array('rso'),
			'sami' => array('smi', 'sami'),
			'sbg' => array('sbg'),
			'siff' => array('vb', 'son'),
			'smjpeg' => array('mjpg'),
			'sox' => array('sox'),
			'spdif' => array('spdif'),
			'srt' => array('srt'),
			'subviewer1' => array('sub'),
			'subviewer' => array('sub'),
			'swf' => array('swf'),
			'tak' => array('tak'),
			'tta' => array('tta'),
			'tty' => array('ans', 'art', 'asc', 'diz', 'ice', 'nfo', 'txt', 'vt'),
			'rcv' => array('rcv'),
			'vivo' => array('viv'),
			'voc' => array('voc'),
			'vplayer' => array('txt'),
			'vqf' => array('vqf', 'vql', 'vqe'),
			'wav' => array('wav'),
			'w64' => array('w64'),
			'webvtt' => array('vtt'),
			'wtv' => array('wtv'),
			'wv' => array('wv'),
			'yop' => array('yop'),
			'yuv4mpegpipe' => array('y4m'),
		);
		
		/**
		 * Returns an array of extensions assiciated with this format.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $format 
		 * @return mixed Returns false if no matching extensions are found, otherwise returns the list 
		 *	of extensions in an array.
		 */
		public static function toExtensions($format)
		{
			$format = strtolower($format);
			return isset(self::$format_to_extensions[$format]) === true ? self::$format_to_extensions[$format] : false;
		}
		
		/**
		 * Attempts to get a "best guess" extension from all compatible extensions.
		 * The way this works is that if an exact match of the format is that extension, then that is returned,
		 * if not then the first extension in the matched extensions is returned.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $format 
		 * @return mixed Returns false if no extension is found, otherwise the extension is returned as a string.
		 */
		public static function toBestGuessExtension($format)
		{
			$format = strtolower($format);
			$extensions = self::toExtensions($format);
			
			if($extensions === false)
			{
				return false;
			}
			else if(in_array($format, $extensions) === true)
			{
				return $format;
			}
			
			return $extensions[0];
		}
		
	}
