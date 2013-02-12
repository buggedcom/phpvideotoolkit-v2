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
 * @version    $Id: STSC.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * Samples within the media data are grouped into chunks. Chunks can be of
 * different sizes, and the samples within a chunk can have different sizes.
 * The <i>Sample To Chunk Box</i> table can be used to find the chunk that
 * contains a sample, its position, and the associated sample description.
 *
 * The table is compactly coded. Each entry gives the index of the first chunk
 * of a run of chunks with the same characteristics. By subtracting one entry
 * here from the previous one, you can compute how many chunks are in this run.
 * You can convert this to a sample count by multiplying by the appropriate
 * samplesPerChunk.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_STSC extends ISO14496_Box_Full
{
  /** @var Array */
  private $_sampleToChunkTable = array();
  
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
      $this->_sampleToChunkTable[$i] = array
        ("firstChunk" =>
           Transform::fromUInt32BE(substr($data, ($i - 1) * 12, 4)),
         "samplesPerChunk" =>
           Transform::fromUInt32BE(substr($data, $i * 12 - 8, 4)),
         "sampleDescriptionIndex" =>
           Transform::fromUInt32BE(substr($data, $i * 12 - 4, 4)));
  }
  
  /**
   * Returns an array of values. Each entry is an array containing the following
   * keys.
   *   o firstChunk -- an integer that gives the index of the first chunk in
   *     this run of chunks that share the same samplesPerChunk and
   *     sampleDescriptionIndex; the index of the first chunk in a track has the
   *     value 1 (the firstChunk field in the first record of this box has the
   *     value 1, identifying that the first sample maps to the first chunk).
   *   o samplesPerChunk is an integer that gives the number of samples in each
   *     of these chunks.
   *   o sampleDescriptionIndex is an integer that gives the index of the sample
   *     entry that describes the samples in this chunk. The index ranges from 1
   *     to the number of sample entries in the {@link ISO14496_Box_STSD Sample
   *     Description Box}.
   *
   * @return Array
   */
  public function getSampleToChunkTable()
  {
    return $this->_sampleToChunkTable;
  }
}
