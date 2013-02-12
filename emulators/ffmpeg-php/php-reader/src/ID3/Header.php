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
 * @version    $Id: Header.php 107 2008-08-03 19:09:16Z svollbehr $
 */

/**#@+ @ignore */
require_once("ID3/Object.php");
/**#@-*/

/**
 * The first part of the ID3v2 tag is the 10 byte tag header. The header
 * contains information about the tag version and options.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 107 $
 */
final class ID3_Header extends ID3_Object
{
  /** A flag to denote whether or not unsynchronisation is applied on all
      frames */
  const UNSYNCHRONISATION = 128;
  
  /** A flag to denote whether or not the header is followed by an extended
      header */
  const EXTENDEDHEADER = 64;
  
  /** A flag used as an experimental indicator. This flag shall always be set
      when the tag is in an experimental stage. */
  const EXPERIMENTAL = 32;
  
  /**
   * A flag to denote whether a footer is present at the very end of the tag.
   *
   * @since ID3v2.4.0
   */
  const FOOTER = 16;

  /** @var integer */
  private $_version = 4.0;
  
  /** @var integer */
  private $_flags = 0;
  
  /** @var integer */
  private $_size;

  /**
   * Constructs the class with given parameters and reads object related data
   * from the ID3v2 tag.
   *
   * @param Reader $reader The reader object.
   * @param Array $options The options array.
   */
  public function __construct($reader = null, &$options = array())
  {
    parent::__construct($reader, $options);
    
    if ($reader === null)
      return;
    
    $this->_version = $options["version"] =
      $this->_reader->readInt8() + $this->_reader->readInt8() / 10;
    $this->_flags = $this->_reader->readInt8();
    $this->_size = $this->decodeSynchsafe32($this->_reader->readUInt32BE());
  }
  
  /**
   * Returns the tag version number. The version number is in the form of
   * major.revision.
   * 
   * @return integer
   */
  public function getVersion() { return $this->_version; }
  
  /**
   * Sets the tag version number. Supported version numbers are 3.0 and 4.0
   * for ID3v2.3.0 and ID3v2.4.0 standards, respectively.
   * 
   * @param integer $version The tag version number in the form of
   *                major.revision.
   */
  public function setVersion($version)
  {
    $this->setOption("version", $this->_version = $version);
  }
  
  /**
   * Checks whether or not the flag is set. Returns <var>true</var> if the flag
   * is set, <var>false</var> otherwise.
   * 
   * @param integer $flag The flag to query.
   * @return boolean
   */
  public function hasFlag($flag) { return ($this->_flags & $flag) == $flag; }
  
  /**
   * Returns the flags byte.
   * 
   * @return integer
   */
  public function getFlags() { return $this->_flags; }
  
  /**
   * Sets the flags byte.
   * 
   * @param string $flags The flags byte.
   */
  public function setFlags($flags) { $this->_flags = $flags; }
  
  /**
   * Returns the tag size, excluding the header and the footer.
   * 
   * @return integer
   */
  public function getSize() { return $this->_size; }
  
  /**
   * Sets the tag size, excluding the header and the footer. Called
   * automatically upon tag generation to adjust the tag size.
   * 
   * @param integer $size The size of the tag, in bytes.
   */
  public function setSize($size) { $this->_size = $size; }
  
  /**
   * Returns the header/footer raw data without the identifier.
   *
   * @return string
   */
  public function __toString()
  {
    return Transform::toInt8(floor($this->_version)) .
      Transform::toInt8(($this->_version - floor($this->_version)) * 10) .
      Transform::toInt8($this->_flags) .
      Transform::toUInt32BE($this->encodeSynchsafe32($this->_size));
  }
}
