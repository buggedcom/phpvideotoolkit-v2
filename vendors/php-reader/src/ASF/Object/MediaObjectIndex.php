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
 * @version    $Id: MediaObjectIndex.php 108 2008-09-05 17:00:05Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * This top-level ASF object supplies media object indexing information for the
 * streams of an ASF file. It includes stream-specific indexing information
 * based on an adjustable index entry media object count interval. This object
 * can be used to index all the video frames or key frames in a video stream.
 * The index is designed to be broken into blocks to facilitate storage that is
 * more space-efficient by using 32-bit offsets relative to a 64-bit base. That
 * is, each index block has a full 64-bit offset in the block header that is
 * added to the 32-bit offset found in each index entry. If a file is larger
 * than 2^32 bytes, then multiple index blocks can be used to fully index the
 * entire large file while still keeping index entry offsets at 32 bits.
 * 
 * Indices into the <i>Media Object Index Object</i> are in terms of media
 * object numbers, with the first frame for a given stream in the ASF file
 * corresponding to entry 0 in the <i>Media Object Index Object</i>. The
 * corresponding <i>Offset</i> field values of the <i>Index Entry</i> are byte
 * offsets that, when combined with the <i>Block Position</i> value of the
 * Index Block, indicate the starting location in bytes of an ASF Data Packet
 * relative to the start of the first ASF Data Packet in the file.
 * 
 * Any ASF file containing a <i>Media Object Index Object</i> shall also contain
 * a <i>Media Object Index Parameters Object</i> in its
 * {@link ASF_Object_Header ASF Header}.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 108 $
 */
final class ASF_Object_MediaObjectIndex extends ASF_Object
{
  /**
   * Indicates that the index type is Nearest Past Data Packet. The Nearest
   * Past Data Packet indexes point to the data packet whose presentation time
   * is closest to the index entry time.
   */
  const NEAREST_PAST_DATA_PACKET = 1;
  
  /**
   * Indicates that the index type is Nearest Past Media. The Nearest Past
   * Object indexes point to the closest data packet containing an entire object
   * or first fragment of an object.
   */
  const NEAREST_PAST_MEDIA = 2;
  
  /**
   * Indicates that the index type is Nearest Past Cleanpoint. The Nearest Past
   * Cleanpoint indexes point to the closest data packet containing an entire
   * object (or first fragment of an object) that has the Cleanpoint Flag set.
   * 
   * Nearest Past Cleanpoint is the most common type of index.
   */
  const NEAREST_PAST_CLEANPOINT = 3;
  
  /** @var integer */
  private $_indexEntryCountInterval;

  /** @var Array */
  private $_indexSpecifiers = array();

  /** @var Array */
  private $_indexBlocks = array();
  
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
    
    $this->_indexEntryCountInterval = $this->_reader->readUInt32LE();
    $indexSpecifiersCount = $this->_reader->readUInt16LE();
    $indexBlocksCount = $this->_reader->readUInt32LE();
    for ($i = 0; $i < $indexSpecifiersCount; $i++)
      $this->_indexSpecifiers[] = array
        ("streamNumber" => $this->_reader->readUInt16LE(),
         "indexType" => $this->_reader->readUInt16LE());
    for ($i = 0; $i < $indexBlocksCount; $i++) {
      $indexEntryCount = $this->_reader->readUInt32LE();
      $blockPositions = array();
      for ($i = 0; $i < $indexSpecifiersCount; $i++)
        $blockPositions[] = $this->_reader->readInt64LE();
      $offsets = array();
      for ($i = 0; $i < $indexSpecifiersCount; $i++)
        $offsets[] = $this->_reader->readUInt32LE();
      $this->_indexBlocks[] = array
        ("blockPositions" => $blockPositions,
         "indexEntryOffsets" => $offsets);
    }
  }

  /**
   * Returns the interval between each index entry in number of media objects.
   * 
   * @return integer
   */
  public function getIndexEntryCountInterval()
  {
    return $this->_indexEntryCountInterval;
  }
  
  /**
   * Returns an array of index specifiers. Each entry consists of the following
   * keys.
   * 
   *   o streamNumber -- Specifies the stream number that the <i>Index
   *     Specifiers</i> refer to. Valid values are between 1 and 127.
   * 
   *   o indexType -- Specifies the type of index.
   *
   * @return Array
   */
  public function getIndexSpecifiers() { return $this->_indexSpecifiers; }
  
  /**
   * Returns an array of index entries. Each entry consists of the following
   * keys.
   * 
   *   o blockPositions -- Specifies a list of byte offsets of the beginnings of
   *     the blocks relative to the beginning of the first Data Packet (for
   *     example, the beginning of the Data Object + 50 bytes).
   * 
   *   o indexEntryOffsets -- Specifies the offset. An offset value of
   *     0xffffffff indicates an invalid offset value.
   *
   * @return Array
   */
  public function getIndexBlocks() { return $this->_indexBlocks; }
}
