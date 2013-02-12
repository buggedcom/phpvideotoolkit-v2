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
 * @version    $Id: TKHD.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * The <i>Track Header Box</i> specifies the characteristics of a single track.
 * Exactly one Track Header Box is contained in a track.
 *
 * In the absence of an edit list, the presentation of a track starts at the
 * beginning of the overall presentation. An empty edit is used to offset the
 * start time of a track.
 * 
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_TKHD extends ISO14496_Box_Full
{
  /** @var integer */
  private $_creationTime;
  
  /** @var integer */
  private $_modificationTime;
  
  /** @var integer */
  private $_trackId;
  
  /** @var integer */
  private $_duration;
  
  /** @var integer */
  private $_width;
  
  /** @var integer */
  private $_height;
  
  /**
   * Indicates that the track is enabled. A disabled track is treated as if it
   * were not present.
   */
  const TRACK_ENABLED = 1;
  
  /** Indicates that the track is used in the presentation. */
  const TRACK_IN_MOVIE = 2;
  
  /** Indicates that the track is used when previewing the presentation. */
  const TRACK_IN_PREVIEW = 4;
  
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
      $this->_trackId = $this->_reader->readUInt32BE();
      $this->_reader->skip(4);
      $this->_duration = $this->_reader->readInt64BE();
    } else {
      $this->_creationTime = $this->_reader->readUInt32BE();
      $this->_modificationTime = $this->_reader->readUInt32BE();
      $this->_trackId = $this->_reader->readUInt32BE();
      $this->_reader->skip(4);
      $this->_duration = $this->_reader->readUInt32BE();
    }
    $this->_reader->skip(52);
    $this->_width =
      ((($tmp = $this->_reader->readUInt32BE()) >> 16) & 0xffff) +
      ($tmp & 0xffff) / 10;
    $this->_height =
      ((($tmp = $this->_reader->readUInt32BE()) >> 16) & 0xffff) +
      ($tmp & 0xffff) / 10;
  }
  
  /**
   * Returns the creation time of this track in seconds since midnight, Jan. 1,
   * 1904, in UTC time.
   * 
   * @return integer
   */
  public function getCreationTime() { return $this->_creationTime; }
  
  /**
   * Returns the most recent time the track was modified in seconds since
   * midnight, Jan. 1, 1904, in UTC time.
   * 
   * @return integer
   */
  public function getModificationTime() { return $this->_modificationTime; }
  
  /**
   * Returns a number that uniquely identifies this track over the entire
   * life-time of this presentation. Track IDs are never re-used and cannot be
   * zero.
   * 
   * @return integer
   */
  public function getTrackId() { return $this->_trackId; }
  
  /**
   * Returns the duration of this track (in the timescale indicated in the
   * {@link MVHD Movie Header Box}). The value of this field is equal to the sum
   * of the durations of all of the track's edits. If there is no edit list,
   * then the duration is the sum of the sample durations, converted into the
   * timescale in the {@link MVHD Movie Header Box}. If the duration of this
   * track cannot be determined then duration is set to all 32-bit maxint.
   * 
   * @return integer
   */
  public function getDuration() { return $this->_duration; }
  
  /**
   * Returns the track's visual presentation width. This needs not be the same
   * as the pixel width of the images; all images in the sequence are scaled to
   * this width, before any overall transformation of the track represented by
   * the matrix. The pixel width of the images is the default value.
   * 
   * @return integer
   */
  public function getWidth() { return $this->_rate; }
  
  /**
   * Returns the track's visual presentation height. This needs not be the same
   * as the pixel height of the images; all images in the sequence are scaled to
   * this height, before any overall transformation of the track represented by
   * the matrix. The pixel height of the images is the default value.
   * 
   * @return integer
   */
  public function getHeight() { return $this->_volume; }
}
