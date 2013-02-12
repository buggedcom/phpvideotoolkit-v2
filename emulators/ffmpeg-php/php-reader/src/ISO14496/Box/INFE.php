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
 * @version    $Id: INFE.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * The <i>Item Information Entry Box</i> contains the entry information.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_INFE extends ISO14496_Box_Full
{
  /** @var integer */
  private $_itemId;

  /** @var integer */
  private $_itemProtectionIndex;

  /** @var string */
  private $_itemName;

  /** @var string */
  private $_contentType;

  /** @var string */
  private $_contentEncoding;

  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader  $reader The reader object.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $this->_itemId = $this->_reader->readUInt16BE();
    $this->_itemProtectionIndex = $this->_reader->readUInt16BE();
    list($this->_itemName, $this->_contentType, $this->_contentEncoding) =
      preg_split
        ("/\\x00/", $this->_reader->read
         ($this->getOffset() + $this->getSize() - $this->_reader->getOffset()));
  }
  
  /**
   * Returns the item identifier. The value is either 0 for the primary resource
   * (e.g. the XML contained in an {@link ISO14496_Box_XML XML Box}) or the ID
   * of the item for which the following information is defined.
   * 
   * @return integer
   */
  public function getItemId() { return $this->_itemId; }
  
  /**
   * Returns the item protection index. The value is either 0 for an unprotected
   * item, or the one-based index into the {@link ISO14496_Box_IPRO Item
   * Protection Box} defining the protection applied to this item (the first box
   * in the item protection box has the index 1).
   * 
   * @return integer
   */
  public function getItemProtectionIndex()
  {
    return $this->_itemProtectionIndex;
  }
  
  /**
   * Returns the symbolic name of the item.
   * 
   * @return string
   */
  public function getItemName() { return $this->_itemName; }
  
  /**
   * Returns the MIME type for the item.
   * 
   * @return string
   */
  public function getContentType() { return $this->_contentType; }
  
  /**
   * Returns the optional content encoding type as defined for Content-Encoding
   * for HTTP /1.1. Some possible values are <i>gzip</i>, <i>compress</i> and
   * <i>deflate</i>. An empty string indicates no content encoding.
   * 
   * @return string
   */
  public function getContentEncoding() { return $this->_contentEncoding; }
}
