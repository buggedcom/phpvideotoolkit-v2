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
 * @version    $Id: TFRA.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * Each entry contains the location and the presentation time of the random
 * accessible sample. It indicates that the sample in the entry can be random
 * accessed. Note that not every random accessible sample in the track needs to
 * be listed in the table.
 *
 * The absence of the <i>Track Fragment Random Access Box</i> does not mean that
 * all the samples are sync samples. Random access information in the
 * {@link ISO14496_Box_TRUN Track Fragment Run Box},
 * {@link ISO14496_Box_TRAF Track Fragment Box} and
 * {@link ISO14496_Box_TREX Track Fragment Box} shall be set appropriately
 * regardless of the presence of this box.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_TFRA extends ISO14496_Box_Full
{
  /** @var integer */
  private $_trackId;

  /** @var Array */
  private $_degradationPriorityTable = array();

  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader  $reader The reader object.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $this->_trackId = $this->_reader->readUInt32BE();
    
    $trafNumberSize = (($tmp = $this->_reader->readUInt32BE()) >> 4) & 0x3;
    $trunNumberSize = ($tmp >> 2) & 0x3;
    $sampleNumberSize = $tmp & 0x3;
    $entryCount = $this->_reader->readUInt32BE();
    for ($i = 1; $i <= $entryCount; $i++) {
      $entry = array();
      if ($this->getVersion() == 1) {
        $entry["time"] = $this->_reader->readInt64BE();
        $entry["moofOffset"] = $this->_reader->readInt64BE();
      } else {
        $entry["time"] = $this->_reader->readUInt32BE();
        $entry["moofOffset"] = $this->_reader->readUInt32BE();
      }
      $entry["trafNumber"] =
        ($trafNumberSize == 4 ? $this->_reader->readUInt32BE() :
         ($trafNumberSize == 8 ? $this->_reader->readInt64BE() : 0));
      $entry["trunNumber"] =
        ($trunNumberSize == 4 ? $this->_reader->readUInt32BE() :
         ($trunNumberSize == 8 ? $this->_reader->readInt64BE() : 0));
      $entry["sampleNumber"] =
        ($sampleNumberSize == 4 ? $this->_reader->readUInt32BE() :
         ($sampleNumberSize == 8 ? $this->_reader->readInt64BE() : 0));
      $this->_degradationPriorityTable[$i] = $entry;
    }
  }
  
  /**
   * Returns the track identifier.
   * 
   * @return integer
   */
  public function getTrackId() { return $this->_trackId; }
  
  /**
   * Returns an array of entries. Each entry is an array containing the
   * following keys.
   *   o time -- a 32 or 64 bits integer that indicates the presentation time of
   *     the random access sample in units defined in the
   *     {@link ISO14496_Box_MDHD Media Header Box} of the associated track.
   *   o moofOffset -- a 32 or 64 bits integer that gives the offset of the
   *     {@link ISO14496_Box_MOOF Movie Fragment Box} used in this entry. Offset
   *     is the byte-offset between the beginning of the file and the beginning
   *     of the Movie Fragment Box.
   *   o trafNumber -- indicates the {@link ISO14496_Box_TRAF Track Fragment
   *     Box} number that contains the random accessible sample. The number
   *     ranges from 1 (the first traf is numbered 1) in each Track Fragment
   *     Box.
   *   o trunNumber -- indicates the {@link ISO14496_Box_TRUN Track Fragment Run
   *     Box} number that contains the random accessible sample. The number
   *     ranges from 1 in each Track Fragment Run Box.
   *   o sampleNumber -- indicates the sample number that contains the random
   *     accessible sample. The number ranges from 1 in each Track Fragment Run
   *     Box.
   * 
   * @return Array
   */
  public function getDegradationPriorityTable()
  {
    return $this->_degradationPriorityTable;
  }
}
