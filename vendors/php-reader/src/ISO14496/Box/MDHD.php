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
 * @version    $Id: MDHD.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * The <i>Media Header Box</i> declares overall information that is
 * media-independent, and relevant to characteristics of the media in a track.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_MDHD extends ISO14496_Box_Full
{
  /** @var integer */
  private $_creationTime;

  /** @var integer */
  private $_modificationTime;

  /** @var integer */
  private $_timescale;

  /** @var integer */
  private $_duration;

  /** @var string */
  private $_language;

  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader  $reader The reader object.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    if ($this->getVersion() == 1) {
      $this->_creationTime = $this->_reader->readInt64BE();
      $this->_modificationTime = $this->_reader->readInt64BE();
      $this->_timescale = $this->_reader->readUInt32BE();
      $this->_duration = $this->_reader->readInt64BE();
    } else {
      $this->_creationTime = $this->_reader->readUInt32BE();
      $this->_modificationTime = $this->_reader->readUInt32BE();
      $this->_timescale = $this->_reader->readUInt32BE();
      $this->_duration = $this->_reader->readUInt32BE();
    }
    $this->_language =
      chr(((($tmp = $this->_reader->readUInt16BE()) >> 10) & 0x1f) + 0x60) .
      chr((($tmp >> 5) & 0x1f) + 0x60) . chr(($tmp & 0x1f) + 0x60);
  }
  
  /**
   * Returns the creation time of the media in this track, in seconds since
   * midnight, Jan. 1, 1904, in UTC time.
   * 
   * @return integer
   */
  public function getCreationTime() { return $this->_creationTime; }
  
  /**
   * Returns the most recent time the media in this track was modified in
   * seconds since midnight, Jan. 1, 1904, in UTC time.
   * 
   * @return integer
   */
  public function getModificationTime() { return $this->_modificationTime; }
  
  /**
   * Returns the time-scale for this media. This is the number of time units
   * that pass in one second. For example, a time coordinate system that
   * measures time in sixtieths of a second has a time scale of 60.
   * 
   * @return integer
   */
  public function getTimescale() { return $this->_timescale; }
  
  /**
   * Returns the duration of this media (in the scale of the timescale).
   * 
   * @return integer
   */
  public function getDuration() { return $this->_duration; }
  
  /**
   * Returns the three byte language code to describe the language of this
   * media, according to {@link http://www.loc.gov/standards/iso639-2/
   * ISO 639-2/T}.
   * 
   * @return string
   */
  public function getLanguage() { return $this->_language; }
}
