<?php
    
    /**
     * This file is part of the PHP Video Toolkit v2 package.
     *
     * @author Oliver Lillie (aka buggedcom) <publicmail@buggedcom.co.uk>
     * @license Dual licensed under MIT and GPLv2
     * @copyright Copyright (c) 2008-2013 Oliver Lillie <http://www.buggedcom.co.uk>
     * @package PHPVideoToolkit V2
     * @version 2.0.1
     * @uses ffmpeg http://ffmpeg.sourceforge.net/
     */
     
    namespace PHPVideoToolkit;

    /**
     * This class provides a wrapper for detecing file mime type, depending on what functionality is available
     * on the installed system.
     *
     * @access public
     * @author Oliver Lillie
     * @author Jorrit Schippers
     * @package default
     */
    class Mime
    {
        public static function get($path)
        {
            return mime_content_type($path);
        }
    }

    /*
       mime_content_type from the upgrade php library.
       Simulates the mime_magic extension. Was originally implemented for
       [http://nanoweb.si.kz/], but that mime magic data reading was
       reinjected for this version (more unclean, though).

       It uses the system-wide "mime_magic" file to do this. See file(1).
       On Windows you might need to install it first.
   
       Also simulates the image type/mime/ext functions.
    */

    #-- mime-magic, type will be detected by analyzing the content
    if (!function_exists("mime_content_type")) {
       function mime_content_type($fn) {

          static $mime_magic_data;

          #-- fallback
          $type = false;

          #-- read in first 3K of given file
          if (is_dir($fn)) {
             return("httpd/unix-directory");
          }
          elseif (is_resource($fn) || ($fn = @fopen($fn, "rb"))) {
             $bin = fread($fn, $maxlen=3072);
             fclose($fn);
          }
          elseif (!file_exists($fn)) {
             return false;
          }
          else {
             return("application/octet-stream");   // give up
          }

          #-- use PECL::fileinfo when available
          if (function_exists("finfo_buffer")) {
             if (!isset($mime_magic_data)) {
                $mime_magic_data = finfo_open(MAGIC_MIME);
             }
             $type = finfo_buffer($bin);
             return($type);
          }
      
          #-- read in magic data, when called for the very first time
          if (!isset($mime_content_type)) {
      
             if ((file_exists($fn = ini_get("mime_magic.magicfile")))
              or (file_exists($fn = "/usr/share/misc/magic.mime"))
              or (file_exists($fn = PATH.'application'.DS.'binaries'.DS.'magic'))
              or (file_exists($fn = "/etc/mime-magic"))   )
             {  
                $mime_magic_data = array();

                #-- read in file
                $f = fopen($fn, "r");
                $fd = fread($f, 1<<20);
                fclose($f);
                $fd = str_replace("       ", "\t", $fd);

                #-- look at each entry
                foreach (explode("\n", $fd) as $line) {

                   #-- skip empty lines
                   if (!strlen($line) or ($line[0] == "#") or ($line[0] == "\n")) {
                      continue;
                   }

                   #-- break into four fields at tabs
                   $l = preg_split("/\t+/", $line);
                   @list($pos, $typestr, $magic, $ct) = $l;
    #print_r($l);

                   #-- ignore >continuing lines
                   if ($pos[0] == ">") {
                      continue;
                   }
                   #-- real mime type string?
                   $ct = strtok($ct, " ");
                   if (!strpos($ct, "/")) {
                      continue;
                   }

                   #-- mask given?
                   $mask = 0;
                   if (strpos($typestr, "&")) {
                      $typestr = strtok($typestr, "&");
                      $mask = strtok(" ");
                      if ($mask[0] == "0") {
                         $mask = ($mask[1] == "x") ? hexdec(substr($mask, 2)) : octdec($mask);
                      }
                      else {
                         $mask = (int)$mask;
                      }
                   }

                   #-- strip prefixes
                   if ($magic[0] == "=") {
                      $magic = substr($magic, 1);
                   }

                   #-- convert type
                   if ($typestr == "string") {
                      $magic = stripcslashes($magic);
                      $len = strlen($magic);
                      if ($mask) { 
                         continue;
                      }
                   }
                   #-- numeric values
                   else {

                      if ((ord($magic[0]) < 48) or (ord($magic[0]) > 57)) {
    #echo "\nmagicnumspec=$line\n";
    #var_dump($l);
                         continue;  #-- skip specials like  >, x, <, ^, &
                      }

                      #-- convert string representation into int
                      if ((strlen($magic) >= 4) && ($magic[1] == "x")) {
                         $magic = hexdec(substr($magic, 2));
                      }
                      elseif ($magic[0]) {
                         $magic = octdec($magic);
                      }
                      else {
                         $magic = (int) $magic;
                         if (!$magic) { continue; }   // zero is not a good magic value anyhow
                      }

                      #-- different types               
                      switch ($typestr) {

                         case "byte":
                            $len = 1;
                            break;
                        
                         case "beshort":
                            $magic = ($magic >> 8) | (($magic & 0xFF) << 8);
                         case "leshort":
                         case "short":
                            $len = 2;
                            break;
                     
                         case "belong":
                            $magic = (($magic >> 24) & 0xFF)
                                   | (($magic >> 8) & 0xFF00)
                                   | (($magic & 0xFF00) << 8)
                                   | (($magic & 0xFF) << 24);
                         case "lelong":
                         case "long":
                            $len = 4;
                            break;

                         default:
                            // date, ldate, ledate, leldate, beldate, lebelbe...
                            continue;
                      }
                   }
               
                   #-- add to list
                   $mime_magic_data[] = array($pos, $len, $mask, $magic, trim($ct));
                }
             }
    #print_r($mime_magic_data);
          }
      
      
          #-- compare against each entry from the mime magic database
          foreach ($mime_magic_data as $def) {

             #-- entries are organized as follows
             list($pos, $len, $mask, $magic, $ct) = $def;
         
             #-- ignored entries (we only read first 3K of file for opt. speed)
             if ($pos >= $maxlen) {
                continue;
             }

             $slice = substr($bin, $pos, $len);
             #-- integer comparison value
             if ($mask) {
                $value = hexdec(bin2hex($slice));
                if (($value & $mask) == $magic) {
                   $type = $ct;
                   break;
                }
             }
             #-- string comparison
             else {
                if ($slice == $magic) {
                   $type = $ct;
                   break;
                }
             }
          }// foreach
      
          #-- built-in defaults
          if (!$type) {
      
             #-- some form of xml
             if (strpos($bin, "<"."?xml ") !== false) {
                return("text/xml");
             }
             #-- html
             elseif ((strpos($bin, "<html>") !== false) || (strpos($bin, "<HTML>") !== false)
             || strpos($bin, "<title>") || strpos($bin, "<TITLE>")
             || (strpos($bin, "<!--") !== false) || (strpos($bin, "<!DOCTYPE HTML ") !== false)) {
                $type = "text/html";
             }
             #-- mail msg
             elseif ((strpos($bin, "\nReceived: ") !== false) || strpos($bin, "\nSubject: ")
             || strpos($bin, "\nCc: ") || strpos($bin, "\nDate: ")) {
                $type = "message/rfc822";
             }
             #-- php scripts
             elseif (strpos($bin, "<"."?php") !== false) {
                return("application/x-httpd-php");
             }
             #-- plain text, C source or so
             elseif (strpos($bin, "function ") || strpos($bin, " and ")
             || strpos($bin, " the ") || strpos($bin, "The ")
             || (strpos($bin, "/*") !== false) || strpos($bin, "#include ")) {
                return("text/plain");
             }

             #-- final fallback
             else {
                $type = false;
             }
          }
      
      

          #-- done
          return $type;
       }
    }



    #-- gives Media Type for the index numbers getimagesize() returned
    if (!function_exists("image_type_to_mime_type")) {
       define("IMAGETYPE_GIF", 1);
       define("IMAGETYPE_JPEG", 2);
       define("IMAGETYPE_PNG", 3);
       define("IMAGETYPE_SWF", 4);
       define("IMAGETYPE_PSD", 5);  // post-4.3 from here ...
       define("IMAGETYPE_BMP", 6);
       define("IMAGETYPE_TIFF_II", 7);
       define("IMAGETYPE_TIFF_MM", 8);
       define("IMAGETYPE_JPC", 9);
       define("IMAGETYPE_JP2", 10);
       define("IMAGETYPE_JPX", 11);
       define("IMAGETYPE_JB2", 12);
       define("IMAGETYPE_SWC", 13);
       define("IMAGETYPE_IFF", 14);
       define("IMAGETYPE_WBMP", 15);
       define("IMAGETYPE_XBM", 16);
       define("IMAGETYPE_MNG", 77);
       define("IMAGETYPE_XPM", 88);
       define("IMAGETYPE_ZIF", 90);
       define("IMAGETYPE_PBM", 80);
       define("IMAGETYPE_PGM", 81);
       define("IMAGETYPE_PPM", 82);
       function image_type_to_mime_type($id) {
          static $mime = array(
             IMAGETYPE_GIF => "gif",
             IMAGETYPE_JPEG => "jpeg",
             IMAGETYPE_PNG => "png",
             IMAGETYPE_SWF => "application/x-shockwave-flash",
             IMAGETYPE_BMP => "bmp",
             IMAGETYPE_JP2 => "jp2",
             IMAGETYPE_WBMP => "vnd.wap.wbmp",
             IMAGETYPE_XBM => "xbm",
             IMAGETYPE_PSD => "x-photoshop",
             IMAGETYPE_TIFF_II => "tiff",
             IMAGETYPE_TIFF_MM => "tiff",
             IMAGETYPE_JPC => "application/octet-stream",
             IMAGETYPE_JP2 => "jp2",
    //         IMAGETYPE_JPX => "",
    //         IMAGETYPE_JB2 => "",
             IMAGETYPE_SWC => "application/x-shockwave-flash",
             IMAGETYPE_IFF => "iff",
             IMAGETYPE_XPM => "x-xpm",
             IMAGETYPE_ZIF => "unknown",
             IMAGETYPE_MNG => "video/mng",
             IMAGETYPE_PBM => "x-portable-bitmap",
             IMAGETYPE_PGM => "x-portable-greymap",
             IMAGETYPE_PPM => "x-portable-pixmap",
          );
          if (isset($mime[$id])) {
             $m = $mime[$id];
             strpos($m, "/") || ($m = "image/$m");
          }
          else {
             $m = "image/unknown";
          }
          return($m);
       }
    }

    #-- still in CVS
    if (!function_exists("image_type_to_extension")) {
       function image_type_to_extension($id, $dot=true) {
          static $ext = array(
             0=>false,
             1=>"gif", 2=>"jpeg", 3=>"png",
             "swf", "psd", "bmp",
             "tiff", "tiff",
             "jpc", "jp2", "jpx", "jb2",
             "swc", "wbmp", "xbm",
             77=>"mng", 88=>"xpm", 90=>"zif",
             80=>"pbm", 81=>"pgm", 82=>"ppm",
          );
          $m = $ext[$id];
          if ($m && $dot) {
             $m = ".$m";
          }
          return($m);
       }
    }



    #-- we need this then, too
    if (!function_exists("exif_imagetype")) {
       function exif_imagetype($fn) {
          $magic = array(
             "\211PNG" => IMAGETYPE_PNG,
             "\377\330" => IMAGETYPE_JPEG,
             "GIF89a" => IMAGETYPE_GIF,
             "GIF94z" => IMAGETYPE_ZIF,
             "FWS" => IMAGETYPE_SWF,
             "II" => IMAGETYPE_TIFF_II,
             "MM" => IMAGETYPE_TIFF_MM,
             "/* XPM" => IMAGETYPE_XPM,
             "BM" => IMAGETYPE_BMP,  // also for OS/2
             "\212MNG" => IMAGETYPE_MNG,
             "P1" => IMAGETYPE_PBM,
             "P4" => IMAGETYPE_PBM,
             "P2" => IMAGETYPE_PGM,
             "P5" => IMAGETYPE_PGM,
             "P3" => IMAGETYPE_PPM,
             "P6" => IMAGETYPE_PPM,
          );
          if ($f = fopen($fn, "rb")) {
             $bin = fread($f, 8);
             fclose($f);
             foreach ($magic as $scn=>$id) {
                if (!strncmp($bin, $scn, strlen($scn))) {
                   return $id;
                }
             }
          }
       }
    }
