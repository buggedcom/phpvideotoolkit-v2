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
 * @version    $Id: MetadataLibrary.php 108 2008-09-05 17:00:05Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>Metadata Library Object</i> lets authors store stream-based,
 * language-attributed, multiply defined, and large metadata attributes in a
 * file.
 * 
 * This object supports the same types of metadata as the
 * <i>{@link ASF_Object_Metadata Metadata Object}</i>, as well as attributes
 * with language IDs, attributes that are defined more than once, large
 * attributes, and attributes with the GUID data type.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 108 $
 */
final class ASF_Object_MetadataLibrary extends ASF_Object
{
  /** @var Array */
  private $_descriptionRecords = array();
  
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
      $descriptionRecord = array
        ("languageIndex" => $this->_reader->readUInt16LE(),
         "streamNumber" => $this->_reader->readUInt16LE());
      $nameLength = $this->_reader->readUInt16LE();
      $dataType = $this->_reader->readUInt16LE();
      $dataLength = $this->_reader->readUInt32LE();
      $descriptionRecord["name"] = iconv
        ("utf-16le", $this->getOption("encoding"),
         $this->_reader->readString16LE($nameLength));
      switch ($dataType) {
      case 0: // Unicode string
        $descriptionRecord["data"] = iconv
          ("utf-16le", $this->getOption("encoding"),
           $this->_reader->readString16LE($dataLength));
        break;
      case 1: // BYTE array
        $descriptionRecord["data"] = $this->_reader->read($dataLength);
        break;
      case 2: // BOOL
        $descriptionRecord["data"] = $this->_reader->readUInt16LE() == 1;
        break;
      case 3: // DWORD
        $descriptionRecord["data"] = $this->_reader->readUInt32LE();
        break;
      case 4: // QWORD
        $descriptionRecord["data"] = $this->_reader->readInt64LE();
        break;
      case 5: // WORD
        $descriptionRecord["data"] = $this->_reader->readUInt16LE();
        break;
      case 6: // GUID
        $descriptionRecord["data"] = $this->_reader->readGUID();
        break;
      }
      $this->_descriptionRecords[] = $descriptionRecord;
    }
  }
  
  /**
   * Returns an array of description records. Each record consists of the
   * following keys.
   * 
   *   o languageIndex -- Specifies the index into the <i>Language List
   *     Object</i> that identifies the language of this attribute. If there is
   *     no <i>Language List Object</i> present, this field is zero.
   * 
   *   o streamNumber -- Specifies whether the entry applies to a specific
   *     digital media stream or whether it applies to the whole file. A value
   *     of 0 in this field indicates that it applies to the whole file;
   *     otherwise, the entry applies only to the indicated stream number. Valid
   *     values are between 1 and 127.
   * 
   *   o name -- Specifies the name that identifies the attribute being
   *     described.
   * 
   *   o data -- Specifies the actual metadata being stored.
   * 
   * @return Array
   */
  public function getDescriptionRecords() { return $this->_descriptionRecords; }
}
