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
 * @version    $Id: GroupMutualExclusion.php 108 2008-09-05 17:00:05Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>Group Mutual Exclusion Object</i> is used to describe mutual exclusion
 * relationships between groups of streams. This object is organized in terms of
 * records, each containing one or more streams, where a stream in record N
 * cannot coexist with a stream in record M for N != M (however, streams in the
 * same record can coexist). This mutual exclusion object would be used
 * typically for the purpose of language mutual exclusion, and a record would
 * consist of all streams for a particular language.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 108 $
 */
final class ASF_Object_GroupMutualExclusion extends ASF_Object
{
  const MUTEX_LANGUAGE = "d6e22a00-35da-11d1-9034-00a0c90349be";
  const MUTEX_BITRATE = "d6e22a01-35da-11d1-9034-00a0c90349be";
  const MUTEX_UNKNOWN = "d6e22a02-35da-11d1-9034-00a0c90349be";
  
  /** @var string */
  private $_exclusionType;
  
  /** @var Array */
  private $_records = array();
  
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
    $this->_exclusionType = $this->_reader->readGUID();
    $recordCount = $this->_reader->readUInt16LE();
    for ($i = 0; $i < $recordCount; $i++) {
      $streamNumbersCount = $this->_reader->readUInt16LE();
      $streamNumbers = array();
      for ($j = 0; $j < $streamNumbersCount; $j++)
        $streamNumbers[] = array
          ("streamNumbers" => $this->_reader->readUInt16LE());
      $this->_records[] = $streamNumbers;
    }
  }
  
  /**
   * Returns the nature of the mutual exclusion relationship.
   *
   * @return string
   */
  public function getExclusionType() { return $this->_exclusionType; }
  
  /**
   * Returns an array of records. Each record consists of the following keys.
   * 
   *   o streamNumbers -- Specifies the stream numbers for this record. Valid
   *     values are between 1 and 127.
   *
   * @return Array
   */
  public function getRecords() { return $this->_records; }
}
