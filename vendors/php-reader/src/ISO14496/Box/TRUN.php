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
 * @version    $Id: TRUN.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * Within the {@link ISO14496_Box_TRAF Track Fragment Box}, there are zero or
 * more <i>Track Fragment Run Boxes</i>. If the durationIsEmpty flag is set,
 * there are no track runs.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_TRUN extends ISO14496_Box_Full
{
  /** @var integer */
  private $_dataOffset;
  
  /** @var Array */
  private $_samples = array();
  
  /** Indicates the precense of the dataOffset field. */
  const DATA_OFFSET = 0x1;
  
  /**
   * Indicates the precense of the firstSampleFlags field; this over-rides the
   * default flags for the first sample only. This makes it possible to record
   * a group of frames where the first is a key and the rest are difference
   * frames, without supplying explicit flags for every sample. If this flag and
   * field are used, sampleFlags field shall not be present.
   */
  const FIRST_SAMPLE_FLAGS = 0x4;
  
  /**
   * Indicates that each sample has its own duration, otherwise the default is
   * used.
   */
  const SAMPLE_DURATION = 0x100;
  
  /**
   * Indicates that each sample has its own size, otherwise the default is used.
   */
  const SAMPLE_SIZE = 0x200;
  
  /**
   * Indicates that each sample has its own flags, otherwise the default is
   * used.
   */
  const SAMPLE_FLAGS = 0x400;
  
  /**
   * Indicates that each sample has a composition time offset (e.g. as used for
   * I/P/B video in MPEG).
   */
  const SAMPLE_COMPOSITION_TIME_OFFSETS = 0x800;
  
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $flags = $this->_flags;
    $sampleCount = $this->_reader->readUInt32BE();
    
    if ($this->hasFlag(self::DATA_OFFSET))
      $this->_dataOffset = $this->_reader->readInt32BE();
    if ($this->hasFlag(self::FIRST_SAMPLE_FLAGS))
      $this->_flags = $this->_reader->readUInt32BE();
    
    for ($i = 0; $i < $sampleCount; $i++) {
      $sample = array();
      if ($this->hasFlag(self::SAMPLE_DURATION))
        $sample["duration"] = $this->_reader->readUInt32BE();
      if ($this->hasFlag(self::SAMPLE_SIZE))
        $sample["size"] = $this->_reader->readUInt32BE();
      if ($this->hasFlag(self::SAMPLE_FLAGS))
        $sample["flags"] = $this->_reader->readUInt32BE();
      if ($this->hasFlag(self::SAMPLE_COMPOSITION_TIME_OFFSET))
        $sample["compositionTimeOffset"] = $this->_reader->readUInt32BE();
      $this->_samples[] = $sample;
      $this->_flags = $flags;
    }
  }
  
  /**
   * Returns the data offset.
   * 
   * @return integer
   */
  public function getDataOffset()
  {
    return $this->_trackId;
  }
  
  /**
   * Returns the array of samples.
   *
   * @return Array
   */
  public function getSamples()
  {
    return $this->_samples;
  }
}
