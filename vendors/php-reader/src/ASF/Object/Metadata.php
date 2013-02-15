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
 * @subpackage ASF
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: Metadata.php 102 2008-06-23 20:41:20Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>Metadata Object</i> permits authors to store stream-based metadata in
 * a file. This object supports the same types of metadata information as the
 * <i>Extended Content Description Object</i> except that it also allows a
 * stream number to be specified.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 102 $
 */
final class ASF_Object_Metadata extends ASF_Object
{
  /** @var Array */
  private $_descriptions = array();
  
  /**
   * Constructs the class with given parameters and reads object related data
   * from the ASF file.
   *
   * @param Reader $reader  The reader object.
   * @param Array  $options The options array.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $descriptionRecordsCount = $this->_reader->readUInt16LE();
    for ($i = 0; $i < $descriptionRecordsCount; $i++) {
      $this->_reader->skip(2);
      $record = array("streamNumber" => $this->_reader->readUInt16LE());
      $nameLength = $this->_reader->readUInt16LE();
      $dataType = $this->_reader->readUInt16LE();
      $dataLength = $this->_reader->readUInt32LE();
      $record["name"] = iconv
        ("utf-16le", $this->getOption("encoding"),
         $this->_reader->readString16LE($nameLength));
      switch ($dataType) {
      case 0:
        $record["data"] = iconv
          ("utf-16le", $this->getOption("encoding"),
           $this->_reader->readString16LE($dataLength));
        break;
      case 1:
        $record["data"] = $this->_reader->readString16LE($dataLength);
        break;
      case 2:
        $record["data"] = $this->_reader->readUInt16LE() ? true : false;
        break;
      case 3:
        $record["data"] = $this->_reader->readUInt32LE();
        break;
      case 4:
        $record["data"] = $this->_reader->readInt64LE();
        break;
      case 5:
        $record["data"] = $this->_reader->readUInt16LE();
        break;
      }
      $this->_descriptions[] = $record;
    }
  }

  /**
   * Returns the array of description records.
   *
   * @return Array
   */
  public function getDescriptions() { return $this->_descriptions; }
}
