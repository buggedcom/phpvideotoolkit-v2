<?php
/**
 * PHP Reader Library
 *
 * Copyright (c) 2006-2008 The PHP Reader Project Workgroup. All rights
 * reserved.
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
 * @subpackage ASF
 * @copyright  Copyright (c) 2006-2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: ExtendedContentDescription.php 108 2008-09-05 17:00:05Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>ASF_Extended_Content_Description_Object</i> object implementation.
 * This object contains unlimited number of attribute fields giving more
 * information about the file.
 * 
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2006-2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 108 $
 */
final class ASF_Object_ExtendedContentDescription extends ASF_Object
{
  /** @var Array */
  private $_contentDescriptors = array();

  /**
   * Constructs the class with given parameters and reads object related data
   * from the ASF file.
   *
   * @param Reader  $reader The reader object.
   * @param Array  $options The options array.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);

    $contentDescriptorsCount = $this->_reader->readUInt16LE();
    for ($i = 0; $i < $contentDescriptorsCount; $i++) {
      $nameLen = $this->_reader->readUInt16LE();
      $name = iconv
        ("utf-16le", $this->getOption("encoding"),
         $this->_reader->readString16LE($nameLen));
      $valueDataType = $this->_reader->readUInt16LE();
      $valueLen = $this->_reader->readUInt16LE();
      switch ($valueDataType) {
      case 0:
      case 1: // string
        $this->_contentDescriptors[$name] = iconv
          ("utf-16le", $this->getOption("encoding"),
           $this->_reader->readString16LE($valueLen));
        break;
      case 2: // bool
      case 3: // 32-bit integer
        $this->_contentDescriptors[$name] = $this->_reader->readUInt32LE();
        break;
      case 4: // 64-bit integer
        $this->_contentDescriptors[$name] = $this->_reader->readInt64LE();
        break;
      case 5: // 16-bit integer
        $this->_contentDescriptors[$name] = $this->_reader->readUInt16LE();
        break;
      default:
      }
    }
  }

  /**
   * Returns the value of the specified descriptor or <var>false</var> if there
   * is no such descriptor defined.
   *
   * @param  string $name The name of the descriptor (ie the name of the field).
   * @return string|false
   */
  public function getDescriptor($name)
  {
    if (isset($this->_contentDescriptors[$name]))
      return $this->_contentDescriptors[$name];
    return false;
  }
  
  /**
   * Returns an associate array of all the descriptors defined having the names
   * of the descriptors as the keys.
   *
   * @return Array
   */
  public function getDescriptors() { return $this->_contentDescriptors; }
}
