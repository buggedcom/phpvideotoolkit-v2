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
 * @version    $Id: SimpleIndex.php 108 2008-09-05 17:00:05Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * For each video stream in an ASF file, there should be one instance of the
 * <i>Simple Index Object</i>. Additionally, the instances of the <i>Simple
 * Index Object</i> shall be ordered by stream number.
 * 
 * Index entries in the <i>Simple Index Object</i> are in terms of
 * <i>Presentation Times</i>. The corresponding <i>Packet Number</i> field
 * values (of the <i>Index Entry</i>, see below) indicate the packet number of
 * the ASF <i>Data Packet</i> with the closest past key frame. Note that for
 * video streams that contain both key frames and non-key frames, the <i>Packet
 * Number</i> field will always point to the closest past key frame.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 108 $
 */
final class ASF_Object_SimpleIndex extends ASF_Object
{
  /** @var string */
  private $_fileId;

  /** @var integer */
  private $_indexEntryTimeInterval;

  /** @var integer */
  private $_maximumPacketCount;

  /** @var Array */
  private $_indexEntries = array();
  
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
    
    $this->_fileId = $this->_reader->readGUID();
    $this->_indexEntryTimeInterval = $this->_reader->readInt64LE();
    $this->_maximumPacketCount = $this->_reader->readUInt32LE();
    $indexEntriesCount = $this->_reader->readUInt32LE();
    for ($i = 0; $i < $indexEntriesCount; $i++) {
      $this->_indexEntries[] = array
        ("packetNumber" => $this->_reader->readUInt32LE(),
         "packetCount" => $this->_reader->readUInt16LE());
    }
  }

  /**
   * Returns the unique identifier for this ASF file. The value of this field
   * should be changed every time the file is modified in any way. The value of
   * this field may be set to 0 or set to be identical to the value of the
   * <i>File ID</i> field of the <i>Data Object</i> and the <i>Header
   * Object</i>.
   *
   * @return string
   */
  public function getFileId() { return $this->_fileId; }

  /**
   * Returns the time interval between each index entry in 100-nanosecond units.
   * The most common value is 10000000, to indicate that the index entries are
   * in 1-second intervals, though other values can be used as well.
   *
   * @return integer
   */
  public function getIndexEntryTimeInterval()
  {
    return $this->_indexEntryTimeInterval;
  }

  /**
   * Returns the maximum <i>Packet Count</i> value of all <i>Index Entries</i>.
   *
   * @return integer
   */
  public function getMaximumPacketCount() { return $this->_maximumPacketCount; }

  /**
   * Returns an array of index entries. Each entry consists of the following
   * keys.
   * 
   *   o packetNumber -- Specifies the number of the Data Packet associated
   *     with this index entry. Note that for video streams that contain both
   *     key frames and non-key frames, this field will always point to the
   *     closest key frame prior to the time interval.
   * 
   *   o packetCount -- Specifies the number of <i>Data Packets</i> to send at
   *     this index entry. If a video key frame has been fragmented into two
   *     Data Packets, the value of this field will be equal to 2.
   *
   * @return Array
   */
  public function getIndexEntries() { return $this->_indexEntries; }
}
