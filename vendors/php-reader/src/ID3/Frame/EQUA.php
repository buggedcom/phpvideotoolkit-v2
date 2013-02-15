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
 * @version    $Id: EQUA.php 105 2008-07-30 14:56:47Z svollbehr $
 * @deprecated ID3v2.3.0
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
/**#@-*/

/**
 * The <i>Equalisation</i> frame is another subjective, alignment frame. It
 * allows the user to predefine an equalisation curve within the audio file.
 * There may only be one EQUA frame in each tag.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @author     Ryan Butterfield <buttza@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 105 $
 * @deprecated ID3v2.3.0
 */
final class ID3_Frame_EQUA extends ID3_Frame
{
  /** @var Array */
  private $_adjustments;
  
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
      return;
    
    $adjustmentBits = Transform::fromInt8($this->_data[0]);
    if ($adjustmentBits <= 8 || $adjustmentBits > 16)
      throw new ID3_Exception
          ("Unsupported adjustment bit size of: " . $adjustmentBits);
    
    for ($i = 1; $i < strlen($this->_data); $i += 4) {
      $frequency = Transform::fromUInt16BE(substr($this->_data, $i, 2));
      $this->_adjustments[($frequency & 0x7fff)] = 
          ($frequency & 0x8000) == 0x8000 ?
          Transform::fromUInt16BE(substr($this->_data, $i + 2, 2)) :
          -Transform::fromUInt16BE(substr($this->_data, $i + 2, 2));
    }
    ksort($this->_adjustments);
  }
  
  /**
   * Returns the array containing adjustments having frequencies as keys and
   * their corresponding adjustments as values.
   * 
   * @return Array
   */
  public function getAdjustments() { return $this->_adjustments; }
  
  /**
   * Adds a volume adjustment setting for given frequency. The frequency can
   * have a value from 0 to 32767 Hz.
   * 
   * @param integer $frequency The frequency, in hertz.
   * @param integer $adjustment The adjustment, in dB.
   */
  public function addAdjustment($frequency, $adjustment)
  {
    $this->_adjustments[$frequency] = $adjustment;
    ksort($this->_adjustments);
  }
  
  /**
   * Sets the adjustments array. The array must have frequencies as keys and
   * their corresponding adjustments as values. The frequency can have a value
   * from 0 to 32767 Hz. One frequency should only be described once in the
   * frame.
   * 
   * @param Array $adjustments The adjustments array.
   */
  public function setAdjustments($adjustments)
  {
    $this->_adjustments = $adjustments;
    ksort($this->_adjustments);
  }
  
  /**
   * Returns the frame raw data.
   *
   * @return string
   */
  public function __toString()
  {
    $data = Transform::toInt8(16);
    foreach ($this->_adjustments as $frequency => $adjustment)
      $data .= Transform::toUInt16BE
        ($adjustment > 0 ? $frequency | 0x8000 : $frequency & ~0x8000) .
        Transform::toUInt16BE(abs($adjustment));
    $this->setData($data);
    return parent::__toString();
  }
}
