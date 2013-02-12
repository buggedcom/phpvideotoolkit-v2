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
 * @version    $Id: Frame.php 107 2008-08-03 19:09:16Z svollbehr $
 */

/**#@+ @ignore */
require_once("ID3/Object.php");
/**#@-*/

/**
 * A base class for all ID3v2 frames as described in the
 * {@link http://www.id3.org/id3v2.4.0-frames ID3v2 frames document}.
 *
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 107 $
 */
class ID3_Frame extends ID3_Object
{
  /**
   * This flag tells the tag parser what to do with this frame if it is unknown
   * and the tag is altered in any way. This applies to all kinds of
   * alterations, including adding more padding and reordering the frames.
   */
  const DISCARD_ON_TAGCHANGE = 16384;
  
  /**
   * This flag tells the tag parser what to do with this frame if it is unknown
   * and the file, excluding the tag, is altered. This does not apply when the
   * audio is completely replaced with other audio data.
   */
  const DISCARD_ON_FILECHANGE = 8192;
  
  /**
   * This flag, if set, tells the software that the contents of this frame are
   * intended to be read only. Changing the contents might break something,
   * e.g. a signature.
   */
  const READ_ONLY = 4096;
  
  /**
   * This flag indicates whether or not this frame belongs in a group with
   * other frames. If set, a group identifier byte is added to the frame. Every
   * frame with the same group identifier belongs to the same group.
   */
  const GROUPING_IDENTITY = 32;
  
  /**
   * This flag indicates whether or not the frame is compressed. A <i>Data
   * Length Indicator</i> byte is included in the frame.
   *
   * @see DATA_LENGTH_INDICATOR
   */
  const COMPRESSION = 8;
  
  /**
   * This flag indicates whether or not the frame is encrypted. If set, one byte
   * indicating with which method it was encrypted will be added to the frame.
   * See description of the {@link ID3_Frame_ENCR} frame for more information
   * about encryption method registration. Encryption should be done after
   * compression. Whether or not setting this flag requires the presence of a
   * <i>Data Length Indicator</i> depends on the specific algorithm used.
   *
   * @see DATA_LENGTH_INDICATOR
   */
  const ENCRYPTION = 4;
  
  /**
   * This flag indicates whether or not unsynchronisation was applied to this
   * frame.
   *
   * @since ID3v2.4.0
   */
  const UNSYNCHRONISATION = 2;
  
  /**
   * This flag indicates that a data length indicator has been added to the
   * frame.
   *
   * @since ID3v2.4.0
   */
  const DATA_LENGTH_INDICATOR = 1;

  /** @var integer */
  private $_identifier;

  /** @var integer */
  private $_size = 0;

  /** @var integer */
  private $_flags = 0;
  
  /**
   * Raw content of the frame.
   *
   * @var string
   */
  protected $_data = "";

  /**
   * Constructs the class with given parameters and reads object related data
   * from the ID3v2 tag.
   *
   * @todo  Only limited subset of flags are processed.
   * @param Reader $reader The reader object.
   * @param Array $options The options array.
   */
  public function __construct($reader = null, &$options = array())
  {
    parent::__construct($reader, $options);

    if ($reader === null) {
      $this->_identifier = substr(get_class($this), -4);
    } else {
      $this->_identifier = $this->_reader->readString8(4);

      /* ID3v2.3.0 size and flags; convert flags to 2.4.0 format */
      if ($this->getOption("version", 4) < 4) {
        $this->_size = $this->_reader->readUInt32BE();
        $flags = $this->_reader->readUInt16BE();
        if (($flags & 0x8000) == 0x8000)
          $this->_flags |= self::DISCARD_ON_TAGCHANGE;
        if (($flags & 0x4000) == 0x4000)
          $this->_flags |= self::DISCARD_ON_FILECHANGE;
        if (($flags & 0x2000) == 0x2000)
          $this->_flags |= self::READ_ONLY;
        if (($flags & 0x80) == 0x80)
          $this->_flags |= self::COMPRESSION;
        if (($flags & 0x40) == 0x40)
          $this->_flags |= self::ENCRYPTION;
        if (($flags & 0x20) == 0x20)
          $this->_flags |= self::GROUPING_IDENTITY;
      }
      
      /* ID3v2.4.0 size and flags */
      else {
        $this->_size = $this->decodeSynchsafe32($this->_reader->readUInt32BE());
        $this->_flags = $this->_reader->readUInt16BE();
      }
      
      $dataLength = $this->_size;
      if ($this->hasFlag(self::DATA_LENGTH_INDICATOR)) {
        $dataLength = $this->decodeSynchsafe32($this->_reader->readUInt32BE());
        $this->_size -= 4;
      }
      $this->_data = $this->_reader->read($this->_size);
      $this->_size = $dataLength;
      
      if ($this->hasFlag(self::UNSYNCHRONISATION) ||
          $this->getOption("unsyncronisation", false) === true)
        $this->_data = $this->decodeUnsynchronisation($this->_data);
    }
  }

  /**
   * Returns the frame identifier string.
   * 
   * @return string
   */
  public function getIdentifier() { return $this->_identifier; }
  
  /**
   * Sets the frame identifier.
   * 
   * @param string $identifier The identifier.
   */
  public function setIdentifier($identifier)
  {
    $this->_identifier = $identifier;
  }
  
  /**
   * Returns the size of the data in the final frame, after encryption,
   * compression and unsynchronisation. The size is excluding the frame header.
   * 
   * @return integer
   */
  public function getSize() { return $this->_size; }
  
  /**
   * Checks whether or not the flag is set. Returns <var>true</var> if the flag
   * is set, <var>false</var> otherwise.
   * 
   * @param integer $flag The flag to query.
   * @return boolean
   */
  public function hasFlag($flag) { return ($this->_flags & $flag) == $flag; }
  
  /**
   * Returns the frame flags byte.
   * 
   * @return integer
   */
  public function getFlags($flags) { return $this->_flags; }
  
  /**
   * Sets the frame flags byte.
   * 
   * @param string $flags The flags byte.
   */
  public function setFlags($flags) { $this->_flags = $flags; }
  
  /**
   * Sets the frame raw data.
   *
   * @param string $data
   */
  protected function setData($data)
  {
    $this->_data = $data;
    $this->_size = strlen($data);
  }

  /**
   * Returns the frame raw data.
   *
   * @return string
   */
  public function __toString()
  {
    /* ID3v2.3.0 Flags; convert from 2.4.0 format */
    if ($this->getOption("version", 4) < 4) {
      $flags = 0;
      if ($this->hasFlag(self::DISCARD_ON_TAGCHANGE))
        $flags = $flags | 0x8000;
      if ($this->hasFlag(self::DISCARD_ON_FILECHANGE))
        $flags = $flags | 0x4000;
      if ($this->hasFlag(self::READ_ONLY))
        $flags = $flags | 0x2000;
      if ($this->hasFlag(self::COMPRESSION))
        $flags = $flags | 0x80;
      if ($this->hasFlag(self::ENCRYPTION))
        $flags = $flags | 0x40;
      if ($this->hasFlag(self::GROUPING_IDENTITY))
        $flags = $flags | 0x20;
    }

    /* ID3v2.4.0 Flags */
    else
      $flags = $this->_flags;
    
    $size = $this->_size;
    if ($this->getOption("version", 4) < 4)
      $data = $this->_data;
    else {
      $data = $this->encodeUnsynchronisation($this->_data);
      if (($dataLength = strlen($data)) != $size) {
        $size = 4 + $dataLength;
        $data = Transform::toUInt32BE($this->encodeSynchsafe32($this->_size)) .
          $data;
        $flags |= self::DATA_LENGTH_INDICATOR | self::UNSYNCHRONISATION;
        $this->setOption("unsyncronisation", true);
      }
    }
    return Transform::toString8(substr($this->_identifier, 0, 4), 4) .
      Transform::toUInt32BE($this->encodeSynchsafe32($size)) .
      Transform::toUInt16BE($flags) . $data;
  }
}
