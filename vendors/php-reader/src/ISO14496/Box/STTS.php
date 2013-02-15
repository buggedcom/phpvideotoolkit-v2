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
 * @version    $Id: STTS.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * The <i>Decoding Time to Sample Box</i> contains a compact version of a table
 * that allows indexing from decoding time to sample number. Other tables give
 * sample sizes and pointers, from the sample number. Each entry in the table
 * gives the number of consecutive samples with the same time delta, and the
 * delta of those samples. By adding the deltas a complete time-to-sample map
 * may be built.
 *
 * The Decoding Time to Sample Box contains decode time delta's: DT(n+1) = DT(n)
 * + STTS(n) where STTS(n) is the (uncompressed) table entry for sample n.
 *
 * The sample entries are ordered by decoding time stamps; therefore the deltas
 * are all non-negative.
 *
 * The DT axis has a zero origin; DT(i) = SUM(for j=0 to i-1 of delta(j)), and
 * the sum of all deltas gives the length of the media in the track (not mapped
 * to the overall timescale, and not considering any edit list).
 *
 * The {@link ISO14496_Box_ELST Edit List Box} provides the initial CT value if
 * it is non-empty (non-zero).
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_STTS extends ISO14496_Box_Full
{
  /** @var Array */
  private $_timeToSampleTable = array();
  
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
      $this->_timeToSampleTable[$i] = array
        ("sampleCount" =>
           Transform::fromUInt32BE(substr($data, ($i - 1) * 8, 4)),
         "sampleDelta" =>
           Transform::fromUInt32BE(substr($data, $i * 8 - 4, 4)));
  }
  
  /**
   * Returns an array of values. Each entry is an array containing the following
   * keys.
   *   o sampleCount -- an integer that counts the number of consecutive samples
   *     that have the given duration.
   *   o sampleDelta -- an integer that gives the delta of these samples in the
   *     time-scale of the media.
   *
   * @return Array
   */
  public function getTimeToSampleTable()
  {
    return $this->_timeToSampleTable;
  }
}
