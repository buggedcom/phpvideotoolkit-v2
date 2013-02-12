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
 * @version    $Id: SYTC.php 107 2008-08-03 19:09:16Z svollbehr $
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
require_once("ID3/Timing.php");
/**#@-*/

/**
 * For a more accurate description of the tempo of a musical piece, the
 * <i>Synchronised tempo codes</i> frame might be used.
 * 
 * The tempo data consists of one or more tempo codes. Each tempo code consists
 * of one tempo part and one time part. The tempo is in BPM described with one
 * or two bytes. If the first byte has the value $FF, one more byte follows,
 * which is added to the first giving a range from 2 - 510 BPM, since $00 and
 * $01 is reserved. $00 is used to describe a beat-free time period, which is
 * not the same as a music-free time period. $01 is used to indicate one single
 * beat-stroke followed by a beat-free period.
 *
 * The tempo descriptor is followed by a time stamp. Every time the tempo in the
 * music changes, a tempo descriptor may indicate this for the player. All tempo
 * descriptors must be sorted in chronological order. The first beat-stroke in
 * a time-period is at the same time as the beat description occurs. There may
 * only be one SYTC frame in each tag.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @author     Ryan Butterfield <buttza@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 107 $
 */
final class ID3_Frame_SYTC extends ID3_Frame
  implements ID3_Timing
{
  /** Describes a beat-free time period. */
  const BEAT_FREE = 0x00;
  
  /** Indicate one single beat-stroke followed by a beat-free period. */
  const SINGLE_BEAT = 0x01;
  
  /** @var integer */
  private $_format = ID3_Timing::MPEG_FRAMES;
  
  /** @var Array */
  private $_events = array();
  
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

    $offset = 0;
    $this->_format = Transform::fromUInt8($this->_data[$offset++]);
    while ($offset < strlen($this->_data)) {
      $tempo = Transform::fromUInt8($this->_data[$offset++]);
      if ($tempo == 0xff)
        $tempo += Transform::fromUInt8($this->_data[$offset++]);
      $this->_events
        [Transform::fromUInt32BE(substr($this->_data, $offset, 4))] = $tempo;
      $offset += 4;
    }
    ksort($this->_events);
  }
  
  /**
   * Returns the timing format.
   * 
   * @return integer
   */
  public function getFormat() { return $this->_format; }

  /**
   * Sets the timing format.
   * 
   * @see ID3_Timing
   * @param integer $format The timing format.
   */
  public function setFormat($format) { $this->_format = $format; }
  
  /**
   * Returns the time-bpm tempo events.
   * 
   * @return Array
   */
  public function getEvents() { return $this->_events; }
  
  /**
   * Sets the time-bpm tempo events.
   * 
   * @param Array $events The time-bpm tempo events.
   */
  public function setEvents($events)
  {
    $this->_events = $events;
    ksort($this->_events);
  }

  /**
   * Returns the frame raw data.
   *
   * @return string
   */
  public function __toString()
  {
    $data = Transform::toUInt8($this->_format);
    foreach ($this->_events as $timestamp => $tempo) {
      if ($tempo >= 0xff)
        $data .= Transform::toUInt8(0xff) . Transform::toUInt8($tempo - 0xff);
      else
        $data .= Transform::toUInt8($tempo);
      $data .= Transform::toUInt32BE($timestamp);
    }
    parent::setData($data);
    return parent::__toString();
  }
}
