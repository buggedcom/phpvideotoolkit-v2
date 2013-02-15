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
 * @version    $Id: Data.php 108 2008-09-05 17:00:05Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
require_once("ASF/Object/Data/Packet.php");
/**#@-*/

/**
 * The <i>Data Object</i> contains all of the <i>Data Packet</i>s for a file.
 * These Data Packets are organized in terms of increasing send times. A <i>Data
 * Packet</i> can contain interleaved data from several digital media streams.
 * This data can consist of entire objects from one or more streams.
 * Alternatively, it can consist of partial objects (fragmentation).
 * 
 * Capabilities provided within the interleave packet definition include:
 *   o Single or multiple payload types per Data Packet
 *   o Fixed-size Data Packets
 *   o Error correction information (optional)
 *   o Clock information (optional)
 *   o Redundant sample information, such as presentation time stamp (optional)
 *
 * @todo       Implement optional support for ASF Data Packet parsing
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 108 $
 */
final class ASF_Object_Data extends ASF_Object
{
  /** @var string */
  private $_fileId;
  
  /** @var integer */
  private $_totalDataPackets;
  
  /** @var Array */
  private $_dataPackets;
  
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

    $this->_fileId = $this->_reader->readGUID();
    $this->_totalDataPackets = $this->_reader->readInt64LE();
    $this->_reader->skip(2);
    /* Data packets are not supported
     * for ($i = 0; $i < $this->_totalDataPackets; $i++) {
     *   $this->_dataPackets[] = new ASF_Object_Data_Packet($reader);
     * }
     */
  }
  
  /**
   * Returns the unique identifier for this ASF file. The value of this field
   * is changed every time the file is modified in any way. The value of this
   * field is identical to the value of the <i>File ID</i> field of the
   * <i>Header Object</i>.
   *
   * @return string
   */
  public function getFileId() { return $this->_fileId; }
  
  /**
   * Returns the number of ASF Data Packet entries that exist within the <i>Data
   * Object</i>. It must be equal to the <i>Data Packet Count</i> field in the
   * <i>File Properties Object</i>. The value of this field is invalid if the
   * broadcast flag field of the <i>File Properties Object</i> is set to 1.
   *
   * @return integer
   */
  public function getTotalDataPackets() { return $this->_endTime; }
  
  /**
   * Returns an array of Data Packets.
   *
   * @return Array
   */
  public function getDataPackets()
  {
    throw new ASF_Exception("Data packets are not parsed due to optimization.");
  }
}
