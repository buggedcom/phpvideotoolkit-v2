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
 * @version    $Id: ILST.php 101 2008-05-13 20:28:13Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box.php");
/**#@-*/

/**
 * A container box for all the iTunes/iPod specific boxes. A list of well known
 * boxes is provided in the following table. The value for each box is contained
 * in a nested {@link ISO14496_Box_DATA Data Box}.
 * 
 * <ul>
 * <li><b>_nam</b> -- <i>Name of the track</i></li>
 * <li><b>_ART</b> -- <i>Name of the artist</i></li>
 * <li><b>aART</b> -- <i>Name of the album artist</i></li>
 * <li><b>_alb</b> -- <i>Name of the album</i></li>
 * <li><b>_grp</b> -- <i>Grouping</i></li>
 * <li><b>_day</b> -- <i>Year of publication</i></li>
 * <li><b>trkn</b> -- <i>Track number (number/total)</i></li>
 * <li><b>disk</b> -- <i>Disk number (number/total)</i></li>
 * <li><b>tmpo</b> -- <i>BPM tempo</i></li>
 * <li><b>_wrt</b> -- <i>Name of the composer</i></li>
 * <li><b>_cmt</b> -- <i>Comments</i></li>
 * <li><b>_gen</b> -- <i>Genre as string</i></li>
 * <li><b>gnre</b> -- <i>Genre as an ID3v1 code, added by one</i></li>
 * <li><b>cpil</b> -- <i>Part of a compilation (0/1)</i></li>
 * <li><b>tvsh</b> -- <i>Name of the (television) show</i></li>
 * <li><b>sonm</b> -- <i>Sort name of the track</i></li>
 * <li><b>soar</b> -- <i>Sort name of the artist</i></li>
 * <li><b>soaa</b> -- <i>Sort name of the album artist</i></li>
 * <li><b>soal</b> -- <i>Sort name of the album</i></li>
 * <li><b>soco</b> -- <i>Sort name of the composer</i></li>
 * <li><b>sosn</b> -- <i>Sort name of the show</i></li>
 * <li><b>_lyr</b> -- <i>Lyrics</i></li>
 * <li><b>covr</b> -- <i>Cover (or other) artwork binary data</i></li>
 * <li><b>_too</b> -- <i>Information about the software</i></li>
 * </ul>
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 101 $
 * @since      iTunes/iPod specific
 */
final class ISO14496_Box_ILST extends ISO14496_Box
{
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader = null, &$options = array())
  {
    parent::__construct($reader, $options);
    $this->setContainer(true);
    
    if ($reader === null)
      return;
    
    $this->constructBoxes("ISO14496_Box_ILST_Container");
  }
  
  /**
   * Override magic function so that $obj->value on a box will return the data
   * box instead of the data container box.
   *
   * @param string $name The box or field name.
   * @return mixed
   */
  public function __get($name)
  {
    if (strlen($name) == 3)
      $name = "\xa9" . $name;
    if ($name[0] == "_")
      $name = "\xa9" . substr($name, 1, 3);
    if ($this->hasBox($name)) {
      $boxes = $this->getBoxesByIdentifier($name);
      return $boxes[0]->data;
    }
    if (method_exists($this, "get" . ucfirst($name)))
      return call_user_func(array($this, "get" . ucfirst($name)));
    return $this->addBox(new ISO14496_Box_ILST_Container($name))->data;
  }
}

/**
 * Generic iTunes/iPod DATA Box container.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 101 $
 * @since      iTunes/iPod specific
 * @ignore
 */
final class ISO14496_Box_ILST_Container extends ISO14496_Box
{
  public function __construct($reader = null, &$options = array())
  {
    parent::__construct(is_string($reader) ? null : $reader, $options);
    $this->setContainer(true);
    
    if (is_string($reader)) {
      $this->setType($reader);
      $this->addBox(new ISO14496_Box_DATA());
    } else
      $this->constructBoxes();
  }
}

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * A box that contains data for iTunes/iPod specific boxes.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 101 $
 * @since      iTunes/iPod specific
 */
final class ISO14496_Box_DATA extends ISO14496_Box_Full
{
  /** @var mixed */
  private $_value;
  
  /** A flag to indicate that the data is an unsigned 8-bit integer. */
  const INTEGER = 0x0;
  
  /**
   * A flag to indicate that the data is an unsigned 8-bit integer. Different
   * value used in old versions of iTunes.
   */
  const INTEGER_OLD_STYLE = 0x15;
  
  /** A flag to indicate that the data is a string. */
  const STRING = 0x1;
  
  /** A flag to indicate that the data is the contents of an JPEG image. */
  const JPEG = 0xd;
  
  /** A flag to indicate that the data is the contents of a PNG image. */
  const PNG = 0xe;
  
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader = null, &$options = array())
  {
    parent::__construct($reader, $options);
    
    if ($reader === null)
      return;
    
    $this->_reader->skip(4);
    $data = $this->_reader->read
      ($this->getOffset() + $this->getSize() - $this->_reader->getOffset());
    switch ($this->getFlags()) {
    case self::INTEGER:
    case self::INTEGER_OLD_STYLE:
      for ($i = 0;  $i < strlen($data); $i++)
        $this->_value .= Transform::fromInt8($data[$i]);
      break;
    case self::STRING:
    default:
      $this->_value = $data;
    }
  }
  
  /**
   * Returns the value this box contains.
   * 
   * @return mixed
   */
  public function getValue() { return $this->_value; }
  
  /**
   * Sets the value this box contains.
   * 
   * @return mixed
   */
  public function setValue($value, $type = false)
  {
    $this->_value = (string)$value;
    if ($type === false && is_string($value))
      $this->_flags = self::STRING;
    if ($type === false && is_int($value))
      $this->_flags = self::INTEGER;
    if ($type !== false)
      $this->_flags = $type;
  }
  
  /**
   * Override magic function so that $obj->data will return the current box
   * instead of an error. For other values the method will attempt to call a
   * getter method.
   *
   * If there are no getter methods with given name, the method will yield an
   * exception.
   *
   * @param string $name The box or field name.
   * @return mixed
   */
  public function __get($name)
  {
    if ($name == "data")
      return $this;
    if (method_exists($this, "get" . ucfirst($name)))
      return call_user_func(array($this, "get" . ucfirst($name)));
    throw new ISO14496_Exception("Unknown box/field: " . $name);
  }
  
  /**
   * Returns the box raw data.
   *
   * @return string
   */
  public function __toString($data = "")
  {
    switch ($this->getFlags()) {
    case self::INTEGER:
    case self::INTEGER_OLD_STYLE:
      $data = "";
      for ($i = 0;  $i < strlen($this->_value); $i++)
        $data .= Transform::toInt8($this->_value[$i]);
      break;
    case self::STRING:
    default:
      $data = $this->_value;
    }
    return parent::__toString("\0\0\0\0" . $data);
  }
}
