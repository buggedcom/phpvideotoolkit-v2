<?php
/**
 * PHP Reader Library
 *
 * Copyright (c) 2006-2008 The PHP Reader Project Workgroup. All rights
 * reserved.
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
 * @copyright  Copyright (c) 2006-2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: FileProperties.php 102 2008-06-23 20:41:20Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>File Properties Object</i> defines the global characteristics of the
 * combined digital media streams found within the Data Object.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2006-2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 102 $
 */
final class ASF_Object_FileProperties extends ASF_Object
{
  /**
   * Indicates, if set, that a file is in the process of being created (for
   * example, for recording applications), and thus that various values stored
   * in the header objects are invalid. It is highly recommended that
   * post-processing be performed to remove this condition at the earliest
   * opportunity.
   */
  const BROADCAST = 1;
  
  /**
   * Indicates, if set, that a file is seekable. Note that for files containing
   * a single audio stream and a <i>Minimum Data Packet Size</i> field equal to
   * the <i>Maximum Data Packet Size</i> field, this flag shall always be set to
   * 1. For files containing a single audio stream and a video stream or
   * mutually exclusive video streams, this flag is only set to 1 if the file
   * contains a matching <i>Simple Index Object</i> for each regular video
   * stream.
   */
  const SEEKABLE = 2;
  
  /** @var string */
  private $_fileId;

  /** @var integer */
  private $_fileSize;

  /** @var integer */
  private $_creationDate;

  /** @var integer */
  private $_dataPacketsCount;

  /** @var integer */
  private $_playDuration;

  /** @var integer */
  private $_sendDuration;

  /** @var integer */
  private $_preroll;

  /** @var integer */
  private $_flags;

  /** @var integer */
  private $_minimumDataPacketSize;

  /** @var integer */
  private $_maximumDataPacketSize;

  /** @var integer */
  private $_maximumBitrate;
  
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
    $this->_fileSize = $this->_reader->readInt64LE();
    $this->_creationDate = $this->_reader->readInt64LE();
    $this->_dataPacketsCount = $this->_reader->readInt64LE();
    $this->_playDuration = $this->_reader->readInt64LE();
    $this->_sendDuration = $this->_reader->readInt64LE();
    $this->_preroll = $this->_reader->readInt64LE();
    $this->_flags = $this->_reader->readUInt32LE();
    $this->_minimumDataPacketSize = $this->_reader->readUInt32LE();
    $this->_maximumDataPacketSize = $this->_reader->readUInt32LE();
    $this->_maximumBitrate = $this->_reader->readUInt32LE();
  }

  /**
   * Returns the file id field.
   *
   * @return integer
   */
  public function getFileId() { return $this->_fileId; }

  /**
   * Returns the size, in bytes, of the entire file. The value of this field is
   * invalid if the broadcast flag bit in the flags field is set to 1.
   *
   * @return integer
   */
  public function getFileSize() { return $this->_fileSize; }

  /**
   * Returns the date and time of the initial creation of the file. The value is
   * given as the number of 100-nanosecond intervals since January 1, 1601,
   * according to Coordinated Universal Time (Greenwich Mean Time). The value of
   * this field may be invalid if the broadcast flag bit in the flags field is
   * set to 1.
   *
   * @return integer
   */
  public function getCreationDate() { return $this->_creationDate; }

  /**
   * Returns the number of Data Packet entries that exist within the
   * {@link ASF_Object_Data Data Object}. The value of this field is invalid if
   * the broadcast flag bit in the flags field is set to 1.
   *
   * @return integer
   */
  public function getDataPacketsCount() { return $this->_dataPacketsCount; }

  /**
   * Returns the time needed to play the file in 100-nanosecond units. This
   * value should include the duration (estimated, if an exact value is
   * unavailable) of the the last media object in the presentation. The value of
   * this field is invalid if the broadcast flag bit in the flags field is set
   * to 1.
   *
   * @return integer
   */
  public function getPlayDuration() { return $this->_playDuration; }

  /**
   * Returns the time needed to send the file in 100-nanosecond units. This
   * value should include the duration of the last packet in the content. The
   * value of this field is invalid if the broadcast flag bit in the flags field
   * is set to 1.
   *
   * @return integer
   */
  public function getSendDuration() { return $this->_sendDuration; }

  /**
   * Returns the amount of time to buffer data before starting to play the file,
   * in millisecond units. If this value is nonzero, the <i>Play Duration</i>
   * field and all of the payload <i>Presentation Time</i> fields have been
   * offset by this amount. Therefore, player software must subtract the value
   * in the preroll field from the play duration and presentation times to
   * calculate their actual values.
   *
   * @return integer
   */
  public function getPreroll() { return $this->_preroll; }

  /**
   * Checks whether or not the flag is set. Returns <var>true</var> if the flag
   * is set, <var>false</var> otherwise.
   * 
   * @param integer $flag The flag to query.
   * @return boolean
   */
  public function hasFlag($flag) { return ($this->_flags & $flag) == $flag; }
  
  /**
   * Returns the flags field.
   *
   * @return integer
   */
  public function getFlags() { return $this->_flags; }
  
  /**
   * Returns the minimum <i>Data Packet</i> size in bytes. In general, the value
   * of this field is invalid if the broadcast flag bit in the flags field is
   * set to 1. However, the values for the <i>Minimum Data Packet Size</i> and
   * <i>Maximum Data Packet Size</i> fields shall be set to the same value, and
   * this value should be set to the packet size, even when the broadcast flag
   * in the flags field is set to 1.
   * 
   * @return integer
   */
  public function getMinimumDataPacketSize()
  {
    return $this->_minimumDataPacketSize;
  }
  
  /**
   * Returns the maximum <i>Data Packet</i> size in bytes. In general, the value
   * of this field is invalid if the broadcast flag bit in the flags field is
   * set to 1. However, the values for the <i>Minimum Data Packet Size</i> and
   * <i>Maximum Data Packet Size</i> fields shall be set to the same value, and
   * this value should be set to the packet size, even when the broadcast flag
   * in the flags field is set to 1.
   * 
   * @return integer
   */
  public function getMaximumDataPacketSize()
  {
    return $this->_maximumDataPacketSize;
  }
  
  /**
   * Returns the maximum instantaneous bit rate in bits per second for the
   * entire file. This is equal the sum of the bit rates of the individual
   * digital media streams.
   * 
   * @return integer
   */
  public function getMaximumBitrate() { return $this->_maximumBitrate; }
}
