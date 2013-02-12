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
 * @subpackage ISO 14496
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: ILOC.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box.php");
/**#@-*/

/**
 * The <i>The Item Location Box</i> provides a directory of resources in this or
 * other files, by locating their containing file, their offset within that
 * file, and their length. Placing this in binary format enables common handling
 * of this data, even by systems which do not understand the particular metadata
 * system (handler) used. For example, a system might integrate all the
 * externally referenced metadata resources into one file, re-adjusting file
 * offsets and file references accordingly.
 *
 * Items may be stored fragmented into extents, e.g. to enable interleaving. An
 * extent is a contiguous subset of the bytes of the resource; the resource is
 * formed by concatenating the extents. If only one extent is used then either
 * or both of the offset and length may be implied:
 *
 *   o If the offset is not identified (the field has a length of zero), then
 *     the beginning of the file (offset 0) is implied.
 *   o If the length is not specified, or specified as zero, then the entire
 *     file length is implied. References into the same file as this metadata,
 *     or items divided into more than one extent, should have an explicit
 *     offset and length, or use a MIME type requiring a different
 *     interpretation of the file, to avoid infinite recursion.
 *
 * The size of the item is the sum of the extentLengths. Note: extents may be
 * interleaved with the chunks defined by the sample tables of tracks.
 *
 * The dataReferenceIndex may take the value 0, indicating a reference into the
 * same file as this metadata, or an index into the dataReference table.
 *
 * Some referenced data may itself use offset/length techniques to address
 * resources within it (e.g. an MP4 file might be included in this way).
 * Normally such offsets are relative to the beginning of the containing file.
 * The field base offset provides an additional offset for offset calculations
 * within that contained data. For example, if an MP4 file is included within a
 * file formatted to this specification, then normally data-offsets within that
 * MP4 section are relative to the beginning of file; baseOffset adds to those
 * offsets.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_ILOC extends ISO14496_Box
{
  /** @var Array */
  private $_items = array();

  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader  $reader The reader object.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $offsetSize = (($tmp = $this->_reader->readUInt32BE()) >> 28) & 0xf;
    $lengthSize = ($tmp >> 24) & 0xf;
    $baseOffsetSize = ($tmp >> 20) & 0xf;
    $itemCount = $this->_reader->readUInt16BE();
    for ($i = 0; $i < $itemCount; $i++) {
      $item = array();
      $item["itemId"] = $this->_reader->readUInt16BE();
      $item["dataReferenceIndex"] = $this->_reader->readUInt16BE();
      $item["baseOffset"] =
        ($baseOffsetSize == 4 ? $this->_reader->readUInt32BE() :
         ($baseOffsetSize == 8 ? $this->_reader->readInt64BE() : 0));
      $item["extents"] = array();
      for ($j = 0; $j < $extentCount; $j++) {
        $extent = array();
        $extent["offset"] =
          ($offsetSize == 4 ? $this->_reader->readUInt32BE() :
           ($offsetSize == 8 ? $this->_reader->readInt64BE() : 0));
        $extent["length"] =
          ($lengthSize == 4 ? $this->_reader->readUInt32BE() :
           ($lengthSize == 8 ? $this->_reader->readInt64BE() : 0));
        $item["extents"][] = $extent;
      }
      $this->_items[] = $item;
    }
  }
  
  /**
   * Returns the array of items. Each entry has the following keys set: itemId,
   * dataReferenceIndex, baseOffset, and extents.
   * 
   * @return Array
   */
  public function getItems() { return $this->_items; }
}
