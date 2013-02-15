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
 * @subpackage ID3
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: MLLT.php 75 2008-04-14 23:57:21Z svollbehr $
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
/**#@-*/

/**
 * To increase performance and accuracy of jumps within a MPEG audio file,
 * frames with time codes in different locations in the file might be useful.
 * The <i>MPEG location lookup table</i> frame includes references that the
 * software can use to calculate positions in the file.
 *
 * The MPEG frames between reference describes how much the frame counter should
 * be increased for every reference. If this value is two then the first
 * reference points out the second frame, the 2nd reference the 4th frame, the
 * 3rd reference the 6th frame etc. In a similar way the bytes between reference
 * and milliseconds between reference points out bytes and milliseconds
 * respectively.
 *
 * Each reference consists of two parts; a certain number of bits that describes
 * the difference between what is said in bytes between reference and the
 * reality and a certain number of bits that describes the difference between
 * what is said in milliseconds between reference and the reality.
 *
 * There may only be one MLLT frame in each tag.
 *
 * @todo       Data parsing and write support
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 75 $
 */
final class ID3_Frame_MLLT extends ID3_Frame
{
  /** @var integer */
  private $_frames;
  
  /** @var integer */
  private $_bytes;

  /** @var integer */
  private $_milliseconds;
  
  /** @var Array */
  private $_deviation = array();
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   * @param Array $options The options array.
   */
  public function __construct($reader = null, &$options = array())
  {
    parent::__construct($reader, $options);
    
    if ($reader === null)
      throw new ID3_Exception("Write not supported yet");

    $this->_frames = Transform::fromInt16BE(substr($this->_data, 0, 2));
    $this->_bytes = Transform::fromInt32BE(substr($this->_data, 2, 3));
    $this->_milliseconds = Transform::fromInt32BE(substr($this->_data, 5, 3));
    
    $byteDevBits = Transform::fromInt8($this->_data[8]);
    $millisDevBits = Transform::fromInt8($this->_data[9]);
    
    // $data = substr($this->_data, 10);
  }

  /**
   * Returns the number of MPEG frames between reference.
   * 
   * @return integer
   */
  public function getFrames() { return $this->_frames; }
  
  /**
   * Sets the number of MPEG frames between reference.
   * 
   * @param integer $frames The number of MPEG frames.
   */
  public function setFrames($frames) { $this->_frames = $frames; }
  
  /**
   * Returns the number of bytes between reference.
   * 
   * @return integer
   */
  public function getBytes() { return $this->_bytes; }
  
  /**
   * Sets the number of bytes between reference.
   * 
   * @param integer $bytes The number of bytes.
   */
  public function setBytes($bytes) { $this->_bytes = $bytes; }
  
  /**
   * Returns the number of milliseconds between references.
   * 
   * @return integer
   */
  public function getMilliseconds() { return $this->_milliseconds; }
  
  /**
   * Sets the number of milliseconds between references.
   * 
   * @param integer $milliseconds The number of milliseconds.
   */
  public function setMilliseconds($milliseconds)
  {
    return $this->_milliseconds;
  }
  
  /**
   * Returns the deviations as an array. Each value is an array containing two
   * values, ie the deviation in bytes, and the deviation in milliseconds,
   * respectively.
   * 
   * @return Array
   */
  public function getDeviation() { return $this->_deviation; }
  
  /**
   * Sets the deviations array. The array must consist of arrays, each of which
   * having two values, the deviation in bytes, and the deviation in
   * milliseconds, respectively.
   * 
   * @param Array $deviation The deviations array.
   */
  public function setDeviation($deviation) { $this->_deviation = $deviation; }
}
