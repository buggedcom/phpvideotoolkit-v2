<?php
/**
 * PHP Reader Library
 *
 * Copyright (c) 2006-2008 The PHP Reader Project Workgroup. All rights
 * reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *  - Neither the name of the project workgroup nor the names of its
 *    contributors may be used to endorse or promote products derived from this
 *    software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    php-reader
 * @subpackage ASF
 * @copyright  Copyright (c) 2006-2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: Header.php 102 2008-06-23 20:41:20Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object/Container.php");
/**#@-*/

/**
 * The role of the header object is to provide a well-known byte sequence at the
 * beginning of ASF files and to contain all the information that is needed to
 * properly interpret the information within the data object. The header object
 * can optionally contain metadata such as bibliographic information.
 *
 * Of the three top-level ASF objects, the header object is the only one that
 * contains other ASF objects. The header object may include a number of
 * standard objects including, but not limited to:
 *
 *  o File Properties Object -- Contains global file attributes.
 *  o Stream Properties Object -- Defines a digital media stream and its
 *    characteristics.
 *  o Header Extension Object -- Allows additional functionality to be added to
 *    an ASF file while maintaining backward compatibility.
 *  o Content Description Object -- Contains bibliographic information.
 *  o Script Command Object -- Contains commands that can be executed on the
 *    playback timeline.
 *  o Marker Object -- Provides named jump points within a file.
 *
 * Note that objects in the header object may appear in any order. To be valid,
 * the header object must contain a {@link ASF_Object_FileProperties File
 * Properties Object}, a {@link ASF_Object_HeaderExtension Header Extension
 * Object}, and at least one {@link ASF_Object_StreamProperties Stream
 * Properties Object}.
 * 
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2006-2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 102 $
 */
final class ASF_Object_Header extends ASF_Object_Container
{
  const FILE_PROPERTIES = "8cabdca1-a947-11cf-8ee4-00c00c205365";
  const STREAM_PROPERTIES = "b7dc0791-a9b7-11cf-8ee6-00c00c205365";
  const HEADER_EXTENSION = "5fbf03b5-a92e-11cf-8ee3-00c00c205365";
  const CODEC_LIST = "86d15240-311d-11d0-a3a4-00a0c90348f6";
  const SCRIPT_COMMAND = "1efb1a30-0b62-11d0-a39b-00a0c90348f6";
  const MARKER = "f487cd01-a951-11cf-8ee6-00c00c205365";
  const BITRATE_MUTUAL_EXCLUSION = "d6e229dc-35da-11d1-9034-00a0c90349be";
  const ERROR_CORRECTION = "75b22635-668e-11cf-a6d9-00aa0062ce6c";
  const CONTENT_DESCRIPTION = "75b22633-668e-11cf-a6d9-00aa0062ce6c";
  const EXTENDED_CONTENT_DESCRIPTION = "d2d0a440-e307-11d2-97f0-00a0c95ea850";
  const CONTENT_BRANDING = "2211b3fa-bd23-11d2-b4b7-00a0c955fc6e";
  const STREAM_BITRATE_PROPERTIES = "7bf875ce-468d-11d1-8d82-006097c9a2b2";
  const CONTENT_ENCRYPTION = "2211b3fb-bd23-11d2-b4b7-00a0c955fc6e";
  const EXTENDED_CONTENT_ENCRYPTION = "298ae614-2622-4c17-b935-dae07ee9289c";
  const DIGITAL_SIGNATURE = "2211b3fc-bd23-11d2-b4b7-00a0c955fc6e";
  const PADDING = "1806d474-cadf-4509-a4ba-9aabcb96aae8";
  
  /**
   * Constructs the class with given parameters and options.
   *
   * @param Reader $reader  The reader object.
   * @param Array  $options The options array.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $this->_reader->skip(6);
    $this->constructObjects
      (array
       (self::FILE_PROPERTIES => "FileProperties",
        self::STREAM_PROPERTIES => "StreamProperties",
        self::HEADER_EXTENSION => "HeaderExtension",
        self::CODEC_LIST => "CodecList",
        self::SCRIPT_COMMAND => "ScriptCommand",
        self::MARKER => "Marker",
        self::BITRATE_MUTUAL_EXCLUSION => "BitrateMutualExclusion",
        self::ERROR_CORRECTION => "ErrorCorrection",
        self::CONTENT_DESCRIPTION => "ContentDescription",
        self::EXTENDED_CONTENT_DESCRIPTION => "ExtendedContentDescription",
        self::CONTENT_BRANDING => "ContentBranding",
        self::STREAM_BITRATE_PROPERTIES => "StreamBitrateProperties",
        self::CONTENT_ENCRYPTION => "ContentEncryption",
        self::EXTENDED_CONTENT_ENCRYPTION => "ExtendedContentEncryption",
        self::DIGITAL_SIGNATURE => "DigitalSignature",
        self::PADDING => "Padding"));
  }
}
