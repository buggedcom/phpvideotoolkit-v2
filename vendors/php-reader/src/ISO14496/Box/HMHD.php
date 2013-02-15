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
 * @version    $Id: HMHD.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * The <i>Hint Media Header Box</i> header contains general information,
 * independent of the protocol, for hint tracks.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_HMHD extends ISO14496_Box_Full
{
  /** @var integer */
  private $_maxPDUSize;

  /** @var integer */
  private $_avgPDUSize;

  /** @var integer */
  private $_maxBitrate;

  /** @var integer */
  private $_avgBitrate;

  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader  $reader The reader object.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $this->_maxPDUSize = $this->_reader->readUInt16BE();
    $this->_avgPDUSize = $this->_reader->readUInt16BE();
    $this->_maxBitrate = $this->_reader->readUInt32BE();
    $this->_avgBitrate = $this->_reader->readUInt32BE();
  }
  
  /**
   * Returns the size in bytes of the largest PDU in this (hint) stream.
   * 
   * @return integer
   */
  public function getMaxPDUSize() { return $this->_maxPDUSize; }
  
  /**
   * Returns the average size of a PDU over the entire presentation.
   * 
   * @return integer
   */
  public function getAvgPDUSize() { return $this->_avgPDUSize; }
  
  /**
   * Returns the maximum rate in bits/second over any window of one second.
   * 
   * @return integer
   */
  public function getMaxBitrate() { return $this->_maxbitrate; }
  
  /**
   * Returns the average rate in bits/second over the entire presentation.
   * 
   * @return integer
   */
  public function getAvgBitrate() { return $this->_maxbitrate; }
}
