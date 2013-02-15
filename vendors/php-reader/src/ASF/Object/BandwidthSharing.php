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
 * @version    $Id: BandwidthSharing.php 108 2008-09-05 17:00:05Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>Bandwidth Sharing Object</i> indicates streams that share bandwidth in
 * such a way that the maximum bandwidth of the set of streams is less than the
 * sum of the maximum bandwidths of the individual streams. There should be one
 * instance of this object for each set of objects that share bandwidth. Whether
 * or not this object can be used meaningfully is content-dependent.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 108 $
 */
final class ASF_Object_BandwidthSharing extends ASF_Object
{
  const SHARING_EXCLUSIVE = "af6060aa-5197-11d2-b6af-00c04fd908e9";
  const SHARING_PARTIAL = "af6060ab-5197-11d2-b6af-00c04fd908e9";
  
  /** @var string */
  private $_sharingType;
  
  /** @var integer */
  private $_dataBitrate;
  
  /** @var integer */
  private $_bufferSize;
  
  /** @var Array */
  private $_streamNumbers = array();
  
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
    
    $this->_sharingType = $this->_reader->readGUID();
    $this->_dataBitrate = $this->_reader->readUInt32LE();
    $this->_bufferSize  = $this->_reader->readUInt32LE();
    $streamNumbersCount = $this->_reader->readUInt16LE();
    for ($i = 0; $i < $streamNumbersCount; $i++)
      $this->_streamNumbers[] = $this->_reader->readUInt16LE();
  }
  
  /**
   * Returns the type of sharing relationship for this object. Two types are
   * predefined: SHARING_PARTIAL, in which any number of the streams in the
   * relationship may be streaming data at any given time; and
   * SHARING_EXCLUSIVE, in which only one of the streams in the relationship
   * may be streaming data at any given time.
   *
   * @return string
   */
  public function getSharingType() { return $this->_sharingType; }
  
  /**
   * Returns the leak rate R, in bits per second, of a leaky bucket that
   * contains the data portion of all of the streams, excluding all ASF Data
   * Packet overhead, without overflowing. The size of the leaky bucket is
   * specified by the value of the Buffer Size field. This value can be less
   * than the sum of all of the data bit rates in the
   * {@link ASF_Object_ExtendedStreamProperties Extended Stream Properties}
   * Objects for the streams contained in this bandwidth-sharing relationship.
   *
   * @return integer
   */
  public function getDataBitrate() { return $this->_dataBitrate; }
  
  /**
   * Specifies the size B, in bits, of the leaky bucket used in the Data Bitrate
   * definition. This value can be less than the sum of all of the buffer sizes
   * in the {@link ASF_Object_ExtendedStreamProperties Extended Stream
   * Properties} Objects for the streams contained in this bandwidth-sharing
   * relationship.
   *
   * @return integer
   */
  public function getBufferSize() { return $this->_bufferSize; }
  
  /**
   * Returns an array of stream numbers.
   *
   * @return Array
   */
  public function getStreamNumbers() { return $this->_streamNumbers; }
}
