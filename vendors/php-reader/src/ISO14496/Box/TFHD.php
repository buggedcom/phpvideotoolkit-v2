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
 * @version    $Id: TFHD.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * Each movie fragment can add zero or more <i>Track Fragment Header Box</i> to
 * each track; and a track fragment can add zero or more contiguous runs of
 * samples. The track fragment header sets up information and defaults used for
 * those runs of samples.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_TFHD extends ISO14496_Box_Full
{
  /** @var integer */
  private $_trackId;
  
  /** @var integer */
  private $_defaultSampleDescriptionIndex;
  
  /** @var integer */
  private $_defaultSampleDuration;
  
  /** @var integer */
  private $_defaultSampleSize;
  
  /** @var integer */
  private $_defaultSampleFlags;
  
  /**
   * Indicates indicates the presence of the baseDataOffset field. This provides
   * an explicit anchor for the data offsets in each track run (see below). If
   * not provided, the base-dataoffset for the first track in the movie fragment
   * is the position of the first byte of the enclosing Movie Fragment Box, and 
   * for second and subsequent track fragments, the default is the end of the
   * data defined by the preceding fragment. Fragments inheriting their offset
   * in this way must all use the same data-reference (i.e., the data for these
   * tracks must be in the same file).
   */
  const BASE_DATA_OFFSET = 0x1;
  
  /**
   * Indicates the presence of the sampleDescriptionIndex field, which
   * over-rides, in this fragment, the default set up in the
   * {@link ISO14496_Box_TREX Track Extends Box}.
   */
  const SAMPLE_DESCRIPTION_INDEX = 0x2;
  
  /** Indicates the precense of the defaultSampleDuration field. */
  const DEFAULT_SAMPLE_DURATION = 0x8;
  
  /** Indicates the precense of the defaultSampleSize field. */
  const DEFAULT_SAMPLE_SIZE = 0x10;
  
  /** Indicates the precense of the defaultSampleFlags field. */
  const DEFAULT_SAMPLE_DURATION = 0x20;
  
  /**
   * Indicates that the duration provided in either defaultSampleDuration, or by
   * the defaultDuration in the {@link ISO14496_Box_TREX Track Extends Box}, is
   * empty, i.e. that there are no samples for this time interval.
   */
  const DURATION_IS_EMPTY = 0x10000;
  
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader  $reader The reader object.
   * @todo The sample flags could be parsed further
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $this->_trackId = $this->_reader->readUInt32BE();
    if ($this->hasFlag(self::BASE_DATA_OFFSET))
      $this->_baseDataOffset = $this->_reader->readInt64BE();
    if ($this->hasFlag(self::SAMPLE_DESCRIPTION_INDEX))
      $this->_sampleDescriptionIndex = $this->_reader->readUInt32BE();
    if ($this->hasFlag(self::DEFAULT_SAMPLE_DURATION))
      $this->_defaultSampleDuration = $this->_reader->readUInt32BE();
    if ($this->hasFlag(self::DEFAULT_SAMPLE_SIZE))
      $this->_defaultSampleSize = $this->_reader->readUInt32BE();
    if ($this->hasFlag(self::DEFAULT_SAMPLE_FLAGS))
      $this->_defaultSampleFlags = $this->_reader->readUInt32BE();
  }
  
  /**
   * Returns the track identifier.
   * 
   * @return integer
   */
  public function getTrackId()
  {
    return $this->_trackId;
  }
  
  /**
   * Returns the base offset to use when calculating data offsets.
   *
   * @return integer
   */
  public function getBaseDataOffset()
  {
    return $this->_baseDataOffset;
  }
  
  /**
   * Returns the sample description index.
   * 
   * @return integer
   */
  public function getSampleDescriptionIndex()
  {
    return $this->_defaultSampleDescriptionIndex;
  }
  
  /**
   * Returns the default sample duration.
   * 
   * @return integer
   */
  public function getDefaultSampleDuration()
  {
    return $this->_defaultSampleDuration;
  }
  
  /**
   * Returns the default sample size.
   * 
   * @return integer
   */
  public function getDefaultSampleSize()
  {
    return $this->_defaultSampleSize;
  }
  
  /**
   * Returns the default sample flags.
   * 
   * @return integer
   */
  public function getDefaultSampleFlags()
  {
    return $this->_defaultSampleFlags;
  }
}
