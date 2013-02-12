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
 * @version    $Id: STCO.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * The <i>Chunk Offset Box</i> table gives the index of each chunk into the
 * containing file. There are two variants, permitting the use of 32-bit or
 * 64-bit offsets. The latter is useful when managing very large presentations.
 * At most one of these variants will occur in any single instance of a sample
 * table.
 *
 * Offsets are file offsets, not the offset into any box within the file (e.g.
 * {@link ISO14496_Box_MDAT Media Data Box}). This permits referring to media
 * data in files without any box structure. It does also mean that care must be
 * taken when constructing a self-contained ISO file with its metadata
 * ({@link ISO14496_Box_MOOV Movie Box}) at the front, as the size of the 
 * {@link ISO14496_Box_MOOV Movie Box} will affect the chunk offsets to the
 * media data.
 *
 * This box variant contains 32-bit offsets.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_STCO extends ISO14496_Box_Full
{
  /** @var Array */
  private $_chunkOffsetTable = array();
  
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $entryCount = $this->_reader->readUInt32BE();
    $data = $this->_reader->read
      ($this->getOffset() + $this->getSize() - $this->_reader->getOffset());
    for ($i = 1; $i <= $entryCount; $i++)
      $this->_chunkOffsetTable[$i] =
        Transform::fromUInt32BE(substr($data, ($i - 1) * 4, 4));
  }
  
  /**
   * Returns an array of values. Each entry has the entry number as its index
   * and a 32 bit integer that gives the offset of the start of a chunk into
   * its containing media file as its value.
   *
   * @return Array
   */
  public function getChunkOffsetTable() { return $this->_chunkOffsetTable; }
  
  /**
   * Sets an array of chunk offsets. Each entry must have the entry number as
   * its index and a 32 bit integer that gives the offset of the start of a
   * chunk into its containing media file as its value.
   *
   * @param Array $chunkOffsetTable The chunk offset array.
   */
  public function setChunkOffsetTable($chunkOffsetTable)
  {
    $this->_chunkOffsetTable = $chunkOffsetTable;
  }
  
  /**
   * Returns the box raw data.
   *
   * @return string
   */
  public function __toString($data = "")
  {
    $data = Transform::toUInt32BE(count($this->_chunkOffsetTable));
    foreach ($this->_chunkOffsetTable as $chunkOffset)
      $data .= Transform::toUInt32BE($chunkOffset);
    return parent::__toString($data);
  }
}
