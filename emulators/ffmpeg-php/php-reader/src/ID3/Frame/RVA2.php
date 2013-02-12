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
 * @version    $Id: RVA2.php 105 2008-07-30 14:56:47Z svollbehr $
 * @since      ID3v2.4.0
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
/**#@-*/

/**
 * The <i>Relative volume adjustment (2)</i> frame is a more subjective frame than
 * the previous ones. It allows the user to say how much he wants to
 * increase/decrease the volume on each channel when the file is played. The
 * purpose is to be able to align all files to a reference volume, so that you
 * don't have to change the volume constantly. This frame may also be used to
 * balance adjust the audio. The volume adjustment is encoded as a fixed point
 * decibel value, 16 bit signed integer representing (adjustment*512), giving
 * +/- 64 dB with a precision of 0.001953125 dB. E.g. +2 dB is stored as $04 00
 * and -2 dB is $FC 00.
 *
 * There may be more than one RVA2 frame in each tag, but only one with the same
 * identification string.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @author     Ryan Butterfield <buttza@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 105 $
 * @since      ID3v2.4.0
 */
final class ID3_Frame_RVA2 extends ID3_Frame
{
  /**
   * The channel type key.
   * 
   * @see $types
   * @var string
   */
  const channelType = "channelType";

  /**
   * The volume adjustment key. Adjustments are +/- 64 dB with a precision of
   * 0.001953125 dB.
   *
   * @var string
   */
  const volumeAdjustment = "volumeAdjustment";

  /**
   * The peak volume key.
   *
   * @var string
   */
  const peakVolume = "peakVolume";

  /**
   * The list of channel types.
   *
   * @var Array
   */
  public static $types = array
    ("Other", "Master volume", "Front right", "Front left", "Back right",
     "Back left", "Front centre", "Back centre", "Subwoofer");

  /** @var string */
  private $_device;

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

    list ($this->_device, $this->_data) =
      $this->explodeString8($this->_data, 2);

    for ($i = $j = 0; $i < 9; $i++) {
      $this->_adjustments[$i] = array
        (self::channelType => Transform::fromInt8($this->_data[$j++]),
         self::volumeAdjustment =>
           Transform::fromInt16BE(substr($this->_data, $j++, 2)) / 512.0);
      $j++;
      $bitsInPeak = Transform::fromInt8($this->_data[$j++]);
      $bytesInPeak = $bitsInPeak > 0 ? ceil($bitsInPeak / 8) : 0;
      switch ($bytesInPeak) {
      case 8:
      case 7:
      case 6:
      case 5:
        $this->_adjustments[$i][self::peakVolume] =
          Transform::fromInt64BE(substr($this->_data, $j, $bytesInPeak));
        break;
      case 4:
      case 3:
        $this->_adjustments[$i][self::peakVolume] =
          Transform::fromUInt32BE(substr($this->_data, $j, $bytesInPeak));
        break;
      case 2:
        $this->_adjustments[$i][self::peakVolume] =
          Transform::fromUInt16BE(substr($this->_data, $j, $bytesInPeak));
        break;
      case 1:
        $this->_adjustments[$i][self::peakVolume] =
          Transform::fromUInt8(substr($this->_data, $j, $bytesInPeak));
      }
      $j += $bytesInPeak;
    }
  }

  /**
   * Returns the device where the adjustments should apply.
   *
   * @return string
   */
  public function getDevice() { return $this->_device; }

  /**
   * Sets the device where the adjustments should apply.
   *
   * @param string $device The device.
   */
  public function setDevice($device) { $this->_device = $device; }

  /**
   * Returns the array containing volume adjustments for each channel. Volume
   * adjustments are arrays themselves containing the following keys:
   * channelType, volumeAdjustment, peakVolume.
   *
   * @return Array
   */
  public function getAdjustments() { return $this->_adjustments; }

  /**
   * Sets the array of volume adjustments for each channel. Each volume
   * adjustment is an array too containing the following keys: channelType,
   * volumeAdjustment, peakVolume.
   *
   * @param Array $adjustments The volume adjustments array.
   */
  public function setAdjustments($adjustments)
  {
    $this->_adjustments = $adjustments;
  }

  /**
   * Returns the frame raw data.
   *
   * @return string
   */
  public function __toString()
  {
    $data = $this->_device . "\0";
    foreach ($this->_adjustments as $channel) {
      $data .= Transform::toInt8($channel[self::channelType]) .
        Transform::toInt16BE($channel[self::volumeAdjustment] * 512);
      if (abs($channel[self::peakVolume]) <= 0xff)
        $data .= Transform::toInt8(8) .
          Transform::toUInt8($channel[self::peakVolume]);
      else if (abs($channel[self::peakVolume]) <= 0xffff)
        $data .= Transform::toInt8(16) .
          Transform::toUInt16BE($channel[self::peakVolume]);
      else if (abs($channel[self::peakVolume]) <= 0xffffffff)
        $data .= Transform::toInt8(32) .
          Transform::toUInt32BE($channel[self::peakVolume]);
      else
        $data .= Transform::toInt8(64) .
          Transform::toInt64BE($channel[self::peakVolume]); // UInt64
    }
    $this->setData($data);
    return parent::__toString();
  }
}
