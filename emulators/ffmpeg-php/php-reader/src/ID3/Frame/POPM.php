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
 * @version    $Id: POPM.php 105 2008-07-30 14:56:47Z svollbehr $
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
/**#@-*/

/**
 * The purpose of the <i>Popularimeter</i> frame is to specify how good an audio
 * file is. Many interesting applications could be found to this frame such as a
 * playlist that features better audio files more often than others or it could
 * be used to profile a person's taste and find other good files by comparing
 * people's profiles. The frame contains the email address to the user, one
 * rating byte and a four byte play counter, intended to be increased with one
 * for every time the file is played.
 *
 * The rating is 1-255 where 1 is worst and 255 is best. 0 is unknown. If no
 * personal counter is wanted it may be omitted. When the counter reaches all
 * one's, one byte is inserted in front of the counter thus making the counter
 * eight bits bigger in the same away as the play counter
 * {@link ID3_Frame_PCNT}. There may be more than one POPM frame in each tag,
 * but only one with the same email address.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @author     Ryan Butterfield <buttza@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 105 $
 */
final class ID3_Frame_POPM extends ID3_Frame
{
  /** @var string */
  private $_owner;

  /** @var integer */
  private $_rating = 0;

  /** @var integer */
  private $_counter = 0;

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

    list($this->_owner, $this->_data) = $this->explodeString8($this->_data, 2);
    $this->_rating = Transform::fromUInt8($this->_data[0]);
    $this->_data = substr($this->_data, 1);

    if (strlen($this->_data) > 4)
      $this->_counter = Transform::fromInt64BE($this->_data); // UInt64
    else if (strlen($this->_data) > 0)
      $this->_counter = Transform::fromUInt32BE($this->_data);
  }

  /**
   * Returns the owner identifier string.
   *
   * @return string
   */
  public function getOwner() { return $this->_owner; }

  /**
   * Sets the owner identifier string.
   *
   * @param string $owner The owner identifier string.
   */
  public function setOwner($owner) { return $this->_owner = $owner; }

  /**
   * Returns the user rating.
   *
   * @return integer
   */
  public function getRating() { return $this->_rating; }

  /**
   * Sets the user rating.
   *
   * @param integer $rating The user rating.
   */
  public function setRating($rating) { $this->_rating = $rating; }

  /**
   * Returns the counter.
   *
   * @return integer
   */
  public function getCounter() { return $this->_counter; }

  /**
   * Adds counter by one.
   */
  public function addCounter() { $this->_counter++; }

  /**
   * Sets the counter value.
   *
   * @param integer $counter The counter value.
   */
  public function setCounter($counter) { $this->_counter = $counter; }

  /**
   * Returns the frame raw data.
   *
   * @return string
   */
  public function __toString()
  {
    $this->setData
      ($this->_owner . "\0" . Transform::toInt8($this->_rating) .
       ($this->_counter > 0xffffffff ?
        Transform::toInt64BE($this->_counter) :
        ($this->_counter > 0 ? Transform::toUInt32BE($this->_counter) : 0)));
    return parent::__toString();
  }
}
