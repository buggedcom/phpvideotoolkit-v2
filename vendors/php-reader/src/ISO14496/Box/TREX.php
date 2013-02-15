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
 * @version    $Id: TREX.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * The <i>Track Extends Box</i> sets up default values used by the movie
 * fragments. By setting defaults in this way, space and complexity can be saved
 * in each {@link ISO14496_Box_TRAF Track Fragment Box}.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_TREX extends ISO14496_Box_Full
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
    $this->_defaultSampleDescriptionIndex = $this->_reader->readUInt32BE();
    $this->_defaultSampleDuration = $this->_reader->readUInt32BE();
    $this->_defaultSampleSize = $this->_reader->readUInt32BE();
    $this->_defaultSampleFlags = $this->_reader->readUInt32BE();
  }
  
  /**
   * Returns the default track identifier.
   * 
   * @return integer
   */
  public function getTrackId()
  {
    return $this->_trackId;
  }
  
  /**
   * Returns the default sample description index.
   * 
   * @return integer
   */
  public function getDefaultSampleDescriptionIndex()
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
