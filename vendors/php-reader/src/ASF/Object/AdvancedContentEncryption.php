<?php
/**
 * PHP Reader Library
 *
 * Copyright (c) 2008 The PHP Reader Project Workgroup. All rights reserved.
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
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: AdvancedContentEncryption.php 108 2008-09-05 17:00:05Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>Advanced Content Encryption Object</i> lets authors protect content by
 * using Next Generation Windows Media Digital Rights Management for Network
 * Devices.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 108 $
 */
final class ASF_Object_AdvancedContentEncryption extends ASF_Object
{
  const WINDOWS_MEDIA_DRM_NETWORK_DEVICES =
    "7a079bb6-daa4-4e12-a5ca-91d3 8dc11a8d";
  
  /** @var Array */
  private $_contentEncryptionRecords = array();
  
  /**
   * Constructs the class with given parameters and reads object related data
   * from the ASF file.
   *
   * @param Reader $reader  The reader object.
   * @param Array  $options The options array.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    $contentEncryptionRecordsCount = $this->_reader->readUInt16LE();
    for ($i = 0; $i < $contentEncryptionRecordsCount; $i++) {
      $entry = array("systemId" => $this->_reader->readGUID(),
        "systemVersion" => $this->_reader->readUInt32LE(),
        "streamNumbers" => array());
      $encryptedObjectRecordCount = $this->_reader->readUInt16LE();
      for ($j = 0; $j < $encryptedObjectRecordCount; $j++) {
        $this->_reader->skip(4);
        $entry["streamNumbers"][] = $this->_reader->readUInt16LE();
      }
      $dataCount = $this->_reader->readUInt32LE();
      $entry["data"] = $this->_reader->read($dataCount);
      $this->_contentEncryptionRecords[] = $entry;
    }
  }
  
  /**
   * Returns an array of content encryption records. Each record consists of the
   * following keys.
   * 
   *   o systemId -- Specifies the unique identifier for the content encryption
   *     system.
   * 
   *   o systemVersion -- Specifies the version of the content encryption
   *     system.
   * 
   *   o streamNumbers -- An array of stream numbers a particular Content
   *     Encryption Record is associated with.
   * 
   *   o data -- The content protection data for this Content Encryption Record.
   *
   * @return Array
   */
  public function getContentEncryptionRecords()
  {
    return $this->_contentEncryptionRecords;
  }
}
