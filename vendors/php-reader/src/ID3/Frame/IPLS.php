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
 * @version    $Id: IPLS.php 105 2008-07-30 14:56:47Z svollbehr $
 * @deprecated ID3v2.3.0
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
require_once("ID3/Encoding.php");
/**#@-*/

/**
 * The <i>Involved people list</i> is a frame containing the names of those
 * involved, and how they were involved. There may only be one IPLS frame in
 * each tag.
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
final class ID3_Frame_IPLS extends ID3_Frame
  implements ID3_Encoding
{
  /** @var integer */
  private $_encoding = ID3_Encoding::UTF8;

  /** @var Array */
  private $_people = array();

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

    $this->_encoding = Transform::fromUInt8($this->_data[0]);
    $data = substr($this->_data, 1);
    $order = Transform::MACHINE_ENDIAN_ORDER;
    switch ($this->_encoding) {
    case self::UTF16:
      $data = $this->explodeString16($data);
      foreach ($data as &$str)
        $str = Transform::fromString16($str, $order);
      break;
    case self::UTF16BE:
      $data = $this->explodeString16($data);
      foreach ($data as &$str)
        $str = Transform::fromString16BE($str);
      break;
    default:
      $data = $this->explodeString8($data);
    }

    for ($i = 0; $i < count($data) - 1; $i += 2)
      $this->_people[] = array($data[$i] => @$data[$i + 1]);
  }

  /**
   * Returns the text encoding.
   *
   * @return integer
   */
  public function getEncoding() { return $this->_encoding; }

  /**
   * Sets the text encoding.
   *
   * @see ID3_Encoding
   * @param integer $encoding The text encoding.
   */
  public function setEncoding($encoding) { $this->_encoding = $encoding; }

  /**
   * Returns the involved people list as an array. For each person, the array
   * contains an entry, which too is an associate array with involvement as its
   * key and involvee as its value.
   *
   * @return Array
   */
  public function getPeople() { return $this->_people; }

  /**
   * Adds a person with his involvement.
   *
   * @return string
   */
  public function addPerson($involvement, $person)
  {
    $this->_people[] = array($involvement => $person);
  }

  /**
   * Sets the involved people list array. For each person, the array must
   * contain an associate array with involvement as its key and involvee as its
   * value.
   *
   * @param Array $people The involved people list.
   */
  public function setPeople($people) { $this->_people = $people; }

  /**
   * Returns the frame raw data.
   *
   * @return string
   */
  public function __toString()
  {
    $data = Transform::toUInt8($this->_encoding);
    $order = $this->_encoding == self::UTF16 ?
      Transform::MACHINE_ENDIAN_ORDER : Transform::LITTLE_ENDIAN_ORDER;
    foreach ($this->_people as $entry) {
      foreach ($entry as $key => $val) {
        switch ($this->_encoding) {
        case self::UTF16:
        case self::UTF16LE:
          $data .= Transform::toString16($key, $order) . "\0\0" .
                   Transform::toString16($val, $order) . "\0\0";
          break;
        case self::UTF16BE:
          $data .= Transform::toString16BE($key . "\0\0" . $val . "\0\0");
          break;
        default:
          $data .= $key . "\0" . $val . "\0";
        }
      }
    }
    $this->setData($data);
    return parent::__toString();
  }
}
