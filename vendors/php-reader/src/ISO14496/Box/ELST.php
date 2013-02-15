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
 * @version    $Id: ELST.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * The <i>Edit List Box</i> contains an explicit timeline map. Each entry
 * defines part of the track time-line: by mapping part of the media time-line,
 * or by indicating empty time, or by defining a dwell, where a single
 * time-point in the media is held for a period.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_ELST extends ISO14496_Box_Full
{
  /** @var Array */
  private $_entries = array();
  
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
    for ($i = 1; $i <= $entryCount; $i++) {
      $entry = array();
      if ($this->getVersion() == 1) {
        $entry["segmentDuration"] = $this->_reader->readInt64BE();
        $entry["mediaTime"] = $this->_reader->readInt64BE();
      } else {
        $entry["segmentDuration"] = $this->_reader->readUInt32BE();
        $entry["mediaTime"] = $this->_reader->readInt32BE();
      }
      $entry["mediaRate"] = $this->_reader->readInt16BE() +
        $this->_reader->readInt16BE() / 10;
      $this->_entries[] = $entry;
    }
  }
  
  /**
   * Returns an array of entries. Each entry is an array containing the
   * following keys.
   *   o segmentDuration: specifies the duration of this edit segment in units
   *     of the timescale in the {@link ISO14496_Box_MVHD Movie Header Box}.
   *   o mediaTime: the starting time within the media of this edit segment (in
   *     media time scale units, in composition time). If this field is set to
   *     –1, it is an empty edit. The last edit in a track shall never be an
   *     empty edit. Any difference between the duration in the
   *     {@link ISO14496_Box_MVHD Movie Header Box}, and the track's duration is
   *     expressed as an implicit empty edit at the end.
   *   o mediaRate: the relative rate at which to play the media corresponding
   *     to this edit segment. If this value is 0, then the edit is specifying
   *     a dwell: the media at media-time is presented for the segment-duration.
   *     Otherwise this field shall contain the value 1.
   *
   * @return Array
   */
  public function getEntries()
  {
    return $this->_entries;
  }
}
