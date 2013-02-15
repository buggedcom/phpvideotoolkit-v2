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
 * @version    $Id: IndexParameters.php 108 2008-09-05 17:00:05Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>Index Parameters Object</i> supplies information about those streams
 * that are actually indexed (there must be at least one stream in an index) by
 * the {@link ASF_Object_Index Index Object} and how they are being indexed.
 * This object shall be present in the {@link ASF_Object_Header Header Object}
 * if there is an {@link ASF_Object_Index Index Object} present in the file.
 * 
 * An Index Specifier is required for each stream that will be indexed by the
 * {@link ASF_Object_Index Index Object}. These specifiers must exactly match
 * those in the {@link ASF_Object_Index Index Object}.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 108 $
 */
final class ASF_Object_IndexParameters extends ASF_Object
{
  /** @var string */
  private $_indexEntryTimeInterval;
  
  /** @var Array */
  private $_indexSpecifiers = array();
  
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
    
    $this->_indexEntryTimeInterval = $this->_reader->readUInt32LE();
    $indexSpecifiersCount = $this->_reader->readUInt16LE();
    for ($i = 0; $i < $indexSpecifiersCount; $i++) {
      $this->_indexSpecifiers[] = array
        ("streamNumber" => $this->_reader->readUInt16LE(),
         "indexType" => $this->_reader->readUInt16LE());
    }
  }
  
  /**
   * Returns the time interval between index entries in milliseconds. This value
   * cannot be 0.
   *
   * @return integer
   */
  public function getIndexEntryTimeInterval()
  {
    return $this->_indexEntryTimeInterval;
  }
  
  /**
   * Returns an array of index entries. Each entry consists of the following
   * keys.
   * 
   *   o streamNumber -- Specifies the stream number that the Index Specifiers
   *     refer to. Valid values are between 1 and 127.
   * 
   *   o indexType -- Specifies the type of index. Values are as follows:
   *       1 = Nearest Past Data Packet,
   *       2 = Nearest Past Media Object, and
   *       3 = Nearest Past Cleanpoint.
   *     The Nearest Past Data Packet indexes point to the data packet whose
   *     presentation time is closest to the index entry time. The Nearest Past
   *     Object indexes point to the closest data packet containing an entire
   *     object or first fragment of an object. The Nearest Past Cleanpoint
   *     indexes point to the closest data packet containing an entire object
   *     (or first fragment of an object) that has the Cleanpoint Flag set.
   *     Nearest Past Cleanpoint is the most common type of index.
   *
   * @return Array
   */
  public function getIndexSpecifiers() { return $this->_indexSpecifiers; }
}
