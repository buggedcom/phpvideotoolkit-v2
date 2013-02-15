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
 * @version    $Id: OWNE.php 105 2008-07-30 14:56:47Z svollbehr $
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
require_once("ID3/Encoding.php");
/**#@-*/

/**
 * The <i>Ownership frame</i> might be used as a reminder of a made transaction
 * or, if signed, as proof. Note that the {@link ID3_Frame_USER} and
 * {@link ID3_Frame_TOWN} frames are good to use in conjunction with this one.
 *
 * There may only be one OWNE frame in a tag.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @author     Ryan Butterfield <buttza@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 105 $
 */
final class ID3_Frame_OWNE extends ID3_Frame
  implements ID3_Encoding
{
  /** @var integer */
  private $_encoding = ID3_Encoding::UTF8;
  
  /** @var string */
  private $_currency = "EUR";

  /** @var string */
  private $_price;

  /** @var string */
  private $_date;
  
  /** @var string */
  private $_seller;
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   * @param Array $options The options array.
   */
  public function __construct($reader = null, &$options = array())
  {
    parent::__construct($reader, $options);
    
    if ($reader === null)
      return;

    $this->_encoding = Transform::fromUInt8($this->_data[0]);
    list($tmp, $this->_data) =
      $this->explodeString8(substr($this->_data, 1), 2);
    $this->_currency = substr($tmp, 0, 3);
    $this->_price = substr($tmp, 3);
    $this->_date = substr($this->_data, 0, 8);
    $this->_data = substr($this->_data, 8);
    
    switch ($this->_encoding) {
    case self::UTF16:
      $this->_seller = Transform::fromString16($this->_data);
      break;
    case self::UTF16BE:
      $this->_seller = Transform::fromString16BE($this->_data);
      break;
    default:
      $this->_seller = Transform::fromString8($this->_data);
    }
  }

  /**
   * Returns the text encoding.
   * 
   * @return integer
   */
  public function getEncoding() { return $this->_encoding; }
  
  /**
   * Sets the text encoding.
   * 
   * @see ID3_Encoding
   * @param integer $encoding The text encoding.
   */
  public function setEncoding($encoding) { $this->_encoding = $encoding; }
  
  /**
   * Returns the currency used in transaction, encoded according to
   * {@link http://www.iso.org/iso/support/faqs/faqs_widely_used_standards/widely_used_standards_other/currency_codes/currency_codes_list-1.htm
   * ISO 4217} alphabetic currency code.
   * 
   * @return string
   */
  public function getCurrency() { return $this->_currency; }
  
  /**
   * Sets the currency used in transaction, encoded according to
   * {@link http://www.iso.org/iso/support/faqs/faqs_widely_used_standards/widely_used_standards_other/currency_codes/currency_codes_list-1.htm
   * ISO 4217} alphabetic currency code.
   * 
   * @param string $currency The currency code.
   */
  public function setCurrency($currency) { $this->_currency = $currency; }
  
  /**
   * Returns the price as a numerical string using "." as the decimal separator.
   * 
   * @return string
   */
  public function getPrice() { return $this->_price; }
  
  /**
   * Sets the price.
   * 
   * @param integer $price The price.
   */
  public function setPrice($price)
  {
    $this->_price = number_format($price, 2, ".", "");
  }
  
  /**
   * Returns the date of purchase as an 8 character date string (YYYYMMDD).
   * 
   * @return string
   */
  public function getDate() { return $this->_date; }
  
  /**
   * Sets the date of purchase. The date must be an 8 character date string
   * (YYYYMMDD).
   * 
   * @param string $date The date string.
   */
  public function setDate($date) { $this->_date = $date; }
  
  /**
   * Returns the name of the seller.
   * 
   * @return string
   */
  public function getSeller() { return $this->_seller; }
  
  /**
   * Sets the name of the seller using given encoding.
   * 
   * @param string $seller The name of the seller.
   * @param integer $encoding The text encoding.
   */
  public function setSeller($seller, $encoding = false)
  {
    $this->_seller = $seller;
    if ($encoding !== false)
      $this->_encoding = $encoding;
  }
  
  /**
   * Returns the frame raw data.
   *
   * @return string
   */
  public function __toString()
  {
    $data = Transform::toUInt8($this->_encoding) . $this->_currency .
      $this->_price . "\0" . $this->_date;
    switch ($this->_encoding) {
    case self::UTF16:
    case self::UTF16LE:
      $data .= Transform::toString16
        ($this->_seller, $this->_encoding == self::UTF16 ?
         Transform::MACHINE_ENDIAN_ORDER : Transform::LITTLE_ENDIAN_ORDER);
      break;
    case self::UTF16BE:
      $data .= Transform::toString16BE($this->_seller);
      break;
    default:
      $data .= $this->_seller;
    }
    $this->setData($data);
    return parent::__toString();
  }
}
