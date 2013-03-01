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
	class Extensions
	{
		/**
		 * This is a list of recognised extensions to formats.
		 * Taken directly from the libavformat files inside ffmpeg.
		 * This list may not be complete and will need updating from time to time.
		 * Last updated 2013-03-01
		 *
		 * @access public
		 * @author Oliver Lillie
		 */
		static $extensions_to_formats = array (
			'a64' => array('a64'),
			'A64' => array('a64'),
			'aac' => array('aac'),
			'ac3' => array('ace','ac3'),
			'eac3' => array('ace','eac3'),
			'aac,adts' => array('adts'),
			'adx' => array('adx'),
			'aea' => array('aea'),
			'aif' => array('aiff'),
			'aiff' => array('aiff'),
			'afc' => array('aiff'),
			'aifc' => array('aiff'),
			'amr' => array('amr'),
			'ape' => array('ape'),
			'apl' => array('ape'),
			'mac' => array('ape'),
			'aqt' => array('aqtitle'),
			'asf' => array('asf', 'asf_stream'),
			'wmv' => array('asf', 'asf_stream'),
			'wma' => array('asf', 'asf_stream'),
			'ass' => array('ass'),
			'ssa' => array('ass'),
			'ast' => array('ast'),
			'au' => array('au'),
			'avi' => array('avi'),
			'avs' => array('avs', 'avisynth'),
			'avr' => array('avr'),
			'bin' => array('bin'),
			'adf' => array('adf'),
			'idf' => array('idf'),
			'bit' => array('bit'),
			'bmv' => array('bmv'),
			'brstm' => array('brstm'),
			'caf' => array('caf'),
			'cdg' => array('cdg'),
			'cdxl' => array('cdxl'),
			'xl' => array('cdxl'),
			'302' => array('daud'),
			'daud' => array('daud'),
			'dts' => array('dts'),
			'dtshd' => array('dtshd'),
			'dv' => array('dv'),
			'dif' => array('dv'),
			'cdata' => array('ea_cdata'),
			'pgaf' => array('epaf'),
			'fap' => array('epaf'),
			'ffm' => array('ffm'),
			'ffmeta' => array('ffmetadata'),
			'flm' => array('filmstrip'),
			'flac' => array('flac'),
			'flv' => array('flv'),
			'g722' => array('g722'),
			'tco' => array('g723_1'),
			'rco' => array('g723_1'),
			'g729' => array('g729'),
			'gif' => array('gif'),
			'gsm' => array('gsm'),
			'gxf' => array('gxf'),
			'm3u8' => array('hls'),
			'ico' => array('ico'),
			'roq' => array('roq'),
			'lbc' => array('ilbc'),
			'bmp' => array('image2'),
			'dpx' => array('image2'),
			'jls' => array('image2'),
			'jpeg' => array('image2'),
			'jpg' => array('image2'),
			'ljpg' => array('image2'),
			'pam' => array('image2'),
			'pbm' => array('image2'),
			'pcx' => array('image2'),
			'pgm' => array('image2'),
			'pgmyuv' => array('image2'),
			'png' => array('image2'),
			'ppm' => array('image2'),
			'sgi' => array('image2'),
			'tga' => array('image2'),
			'tif' => array('image2'),
			'tiff' => array('image2'),
			'jp2' => array('image2'),
			'j2c' => array('image2'),
			'xwd' => array('image2'),
			'sun' => array('image2'),
			'ras' => array('image2'),
			'rs' => array('image2'),
			'im1' => array('image2'),
			'im8' => array('image2'),
			'im24' => array('image2'),
			'sunras' => array('image2'),
			'xbm' => array('image2'),
			'xface' => array('image2'),
			'cgi' => array('ingenient'),
			'sf' => array('ircam'),
			'ircam' => array('ircam'),
			'ivf' => array('ivf'),
			'jss' => array('jacosub'),
			'js' => array('jacosub'),
			'latm' => array('latm'),
			'669' => array('libmodplug'),
			'abc' => array('libmodplug'),
			'amf' => array('libmodplug'),
			'ams' => array('libmodplug'),
			'dbm' => array('libmodplug'),
			'dmf' => array('libmodplug'),
			'dsm' => array('libmodplug'),
			'far' => array('libmodplug'),
			'it' => array('libmodplug'),
			'mdl' => array('libmodplug'),
			'med' => array('libmodplug'),
			'mid' => array('libmodplug'),
			'mod' => array('libmodplug'),
			'mt2' => array('libmodplug'),
			'mtm' => array('libmodplug'),
			'okt' => array('libmodplug'),
			'psm' => array('libmodplug'),
			'ptm' => array('libmodplug'),
			's3m' => array('libmodplug'),
			'stm' => array('libmodplug'),
			'ult' => array('libmodplug'),
			'umx' => array('libmodplug'),
			'xm' => array('libmodplug'),
			'itgz' => array('libmodplug'),
			'itr' => array('libmodplug'),
			'itz' => array('libmodplug'),
			'mdgz' => array('libmodplug'),
			'mdr' => array('libmodplug'),
			'mdz' => array('libmodplug'),
			's3gz' => array('libmodplug'),
			's3r' => array('libmodplug'),
			's3z' => array('libmodplug'),
			'xmgz' => array('libmodplug'),
			'xmr' => array('libmodplug'),
			'xmz' => array('libmodplug'),
			'nut' => array('libnut', 'nut'),
			'lvf' => array('lvf'),
			'mkv' => array('matroska'),
			'mka' => array('matroska'),
			'webm' => array('webm'),
			'sub' => array('subviewer', 'microdvd', 'mpsub', 'subviewer1'),
			'mmf' => array('mmf'),
			'mov' => array('mov'),
			'3gp' => array('3gp'),
			'mp4' => array('mp4', 'psp'),
			'psp' => array('psp'),
			'3g2' => array('3g2'),
			'm4v' => array('m4v', 'ipod'),
			'm4a' => array('ipod'),
			'ismv' => array('ismv'),
			'isma' => array('ismv'),
			'f4v' => array('f4v'),
			'mp2' => array('mp2', 'mp3'),
			'mp3' => array('mp3', 'mp2'),
			'm2a' => array('mp2', 'mp3'),
			'mpc' => array('mpc'),
			'idx' => array('vobsub'),
			'mpg' => array('mpeg', 'mpeg1video'),
			'mpeg' => array('mpeg', 'mpeg1video'),
			'vob' => array('vob', 'svcd'),
			'dvd' => array('dvd'),
			'ts' => array('mpegts'),
			'm2t' => array('mpegts'),
			'm2ts' => array('mpegts'),
			'mts' => array('mpegts'),
			'mjpg' => array('mjpeg', 'mpjpeg', 'smjpeg'),
			'txt' => array('tty', 'mpl2', 'vplayer'),
			'mpl2' => array('mpl2'),
			'mvi' => array('mvi'),
			'mxf' => array('mxf'),
			'mxg' => array('mxg'),
			'v' => array('nc'),
			'nist' => array('nistsphere'),
			'sph' => array('nistsphere'),
			'ogg' => array('ogg'),
			'ogv' => array('ogg'),
			'spx' => array('ogg'),
			'opus' => array('ogg'),
			'oma' => array('oma'),
			'sw' => array('s16be', 's16le'),
			'sb' => array('s8'),
			'uw' => array('u16be', 'u16le'),
			'ub' => array('u8'),
			'al' => array('alaw'),
			'ul' => array('mulaw'),
			'pjs' => array('pjs'),
			'pvf' => array('pvf'),
			'mlp' => array('mlp'),
			'thd' => array('truehd'),
			'shn' => array('shn'),
			'vc1' => array('vc1'),
			'cavs' => array('cavsvideo'),
			'drc' => array('dirac'),
			'dnxhd' => array('dnxhd'),
			'h261' => array('h261'),
			'h263' => array('h263'),
			'h264' => array('h264'),
			'mjpeg' => array('mjpeg'),
			'm1v' => array('mpeg1video'),
			'm2v' => array('mpeg2video'),
			'yuv' => array('rawvideo'),
			'cif' => array('rawvideo'),
			'qcif' => array('rawvideo'),
			'rgb' => array('rawvideo'),
			'rt' => array('realtext'),
			'rm' => array('rm'),
			'ra' => array('rm'),
			'rso' => array('rso'),
			'smi' => array('sami'),
			'sami' => array('sami'),
			'sbg' => array('sbg'),
			'vb' => array('siff'),
			'son' => array('siff'),
			'sox' => array('sox'),
			'spdif' => array('spdif'),
			'srt' => array('srt'),
			'swf' => array('swf'),
			'tak' => array('tak'),
			'tta' => array('tta'),
			'ans' => array('tty'),
			'art' => array('tty'),
			'asc' => array('tty'),
			'diz' => array('tty'),
			'ice' => array('tty'),
			'nfo' => array('tty'),
			'vt' => array('tty'),
			'rcv' => array('rcv'),
			'viv' => array('vivo'),
			'voc' => array('voc'),
			'vqf' => array('vqf'),
			'vql' => array('vqf'),
			'vqe' => array('vqf'),
			'wav' => array('wav'),
			'w64' => array('w64'),
			'vtt' => array('webvtt'),
			'wtv' => array('wtv'),
			'wv' => array('wv'),
			'yop' => array('yop'),
			'y4m' => array('yuv4mpegpipe'),
		);
		
		/**
		 * Returns an array of formats associated with the related extension.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $format 
		 * @return mixed Returns false if no matching extensions are found, otherwise returns the list 
		 *	of extensions in an array.
		 */
		public static function toFormats($extension)
		{
			$extension = strtolower($extension);
			return isset(self::$extensions_to_formats[$extension]) === true ? self::$extensions_to_formats[$extension] : false;
		}
		
		/**
		 * Attempts to get a "best guess" the format from all compatible formats.
		 * The way this works is that if an exact match of the extension is that format, then that is returned,
		 * if not then the first format in the matched formats is returned.
		 *
		 * @access public
		 * @author Oliver Lillie
		 * @param string $format 
		 * @return mixed Returns false if no extension is found, otherwise the extension is returned as a string.
		 */
		public static function toBestGuessFormat($extension)
		{
			$extension = strtolower($extension);
			$formats = self::toFormats($extension);
			
			if($formats === false)
			{
				return false;
			}
			else if(in_array($extension, $formats) === true)
			{
				return $extension;
			}
			
			return $formats[0];
		}
	}
