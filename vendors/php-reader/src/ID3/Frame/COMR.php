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
 * @version    $Id: COMR.php 105 2008-07-30 14:56:47Z svollbehr $
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
require_once("ID3/Encoding.php");
/**#@-*/

/**
 * The <i>Commercial frame</i> enables several competing offers in the same tag
 * by bundling all needed information. That makes this frame rather complex but
 * it's an easier solution than if one tries to achieve the same result with
 * several frames.
 * 
 * There may be more than one commercial frame in a tag, but no two may be
 * identical.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @author     Ryan Butterfield <buttza@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 105 $
 */
final class ID3_Frame_COMR extends ID3_Frame
  implements ID3_Encoding
{
  /**
   * The delivery types.
   *
   * @var Array
   */
  public static $types = array
    ("Other", "Standard CD album with other songs", "Compressed audio on CD",
     "File over the Internet", "Stream over the Internet", "As note sheets",
     "As note sheets in a book with other sheets", "Music on other media",
     "Non-musical merchandise");

  /** @var integer */
  private $_encoding = ID3_Encoding::UTF8;
  
  /** @var string */
  private $_currency = "EUR";

  /** @var string */
  private $_price;

  /** @var string */
  private $_date;

  /** @var string */
  private $_contact;

  /** @var integer */
  private $_delivery = 0;

  /** @var string */
  private $_seller;

  /** @var string */
  private $_description;

  /** @var string */
  private $_mimeType = false;
  
  /** @var string */
  private $_imageData;
  
  /** @var integer */
  private $_imageSize = 0;  
  
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
    list($pricing, $this->_data) =
      $this->explodeString8(substr($this->_data, 1), 2);
    $this->_currency = substr($pricing, 0, 3);
    $this->_price = substr($pricing, 3);
    $this->_date = substr($this->_data, 0, 8);
    list($this->_contact, $this->_data) =
      $this->explodeString8(substr($this->_data, 8), 2);
    $this->_delivery = Transform::fromUInt8($this->_data[0]);
    $this->_data = substr($this->_data, 1);
    
    switch ($this->_encoding) {
    case self::UTF16:
      list ($this->_seller, $this->_description, $this->_data) =
        $this->explodeString16($this->_data, 3);
      $this->_seller = Transform::fromString16($this->_seller);
      $this->_description = Transform::fromString16($this->_description);
      break;
    case self::UTF16BE:
      list ($this->_seller, $this->_description, $this->_data) =
        $this->explodeString16($this->_data, 3);
      $this->_seller = Transform::fromString16BE($this->_seller);
      $this->_description = Transform::fromString16BE($this->_description);
      break;
    default:
      list ($this->_seller, $this->_description, $this->_data) =
        $this->explodeString8($this->_data, 3);
      $this->_seller = Transform::fromString8($this->_seller);
      $this->_description = Transform::fromString8($this->_description);
    }
    
    if (strlen($this->_data) == 0)
      return;
    
    list($this->_mimeType, $this->_imageData) =
      $this->explodeString8($this->_data, 2);
      
    $this->_imageSize = strlen($this->_imageData);
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
   * Returns the currency code, encoded according to
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
   * In the price string several prices may be concatenated, separated by a "/"
   * character, but there may only be one currency of each type.
   * 
   * @return string
   */
  public function getPrice() { return $this->_price; }
  
  /**
   * Sets the price. The price must use "." as the decimal separator and have
   * multiple values be separated by a "/" character.
   * 
   * @param string $price The price.
   */
  public function setPrice($price)
  {
    $this->_price = $price;
  }
  
  /**
   * Returns the date as an 8 character date string (YYYYMMDD), describing for
   * how long the price is valid.
   * 
   * @return string
   */
  public function getDate() { return $this->_date; }

  /**
   * Sets the date describing for how long the price is valid for. The date must
   * be an 8 character date string (YYYYMMDD).
   * 
   * @param string $date The date string.
   */
  public function setDate($date) { $this->_date = $date; }
  
  /**
   * Returns the contact URL, with which the user can contact the seller.
   * 
   * @return string
   */
  public function getContact() { return $this->_contact; }
  
  /**
   * Sets the contact URL, with which the user can contact the seller.
   * 
   * @param string $contact The contact URL.
   */
  public function setContact($contact) { $this->_contact = $contact; }

  /**
   * Returns the delivery type with whitch the audio was delivered when bought.
   * 
   * @return integer
   */
  public function getDelivery() { return $this->_delivery; }

  /**
   * Sets the delivery type with whitch the audio was delivered when bought.
   * 
   * @param integer $delivery The delivery type code.
   */
  public function setDelivery($delivery) { $this->_delivery = $delivery; }

  /**
   * Returns the name of the seller.
   * 
   * @return string
   */
  public function getSeller() { return $this->_seller; }
  
  /**
   * Sets the name of the seller using given encoding. The seller text encoding
   * must be that of the description text.
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
   * Returns the short description of the product.
   * 
   * @return string
   */
  public function getDescription() { return $this->_description; }

  /**
   * Sets the content description text using given encoding. The description
   * encoding must be that of the seller text.
   * 
   * @param string $description The content description text.
   * @param integer $encoding The text encoding.
   */
  public function setDescription($description, $encoding = false)
  {
    $this->_description = $description;
    if ($encoding !== false)
      $this->_encoding = $encoding;
  }
  
  /**
   * Returns the MIME type of the seller's company logo, if attached, or
   * <var>false</var> otherwise. Currently only "image/png" and "image/jpeg"
   * are allowed.
   * 
   * @return string
   */
  public function getMimeType() { return $this->_mimeType; }
  
  /**
   * Sets the MIME type. Currently only "image/png" and "image/jpeg" are
   * allowed. The MIME type is always ISO-8859-1 encoded.
   * 
   * @param string $mimeType The MIME type.
   */
  public function setMimeType($mimeType) { $this->_mimeType = $mimeType; }
  
  /**
   * Returns the embedded image binary data.
   * 
   * @return string
   */
  public function getImageData() { return $this->_imageData; }
  
  /**
   * Sets the embedded image data. Also updates the image size to correspond the
   * new data.
   * 
   * @param string $imageData The image data.
   */
  public function setImageData($imageData)
  {
    $this->_imageData = $imageData;
    $this->_imageSize = strlen($imageData);
  }
  
  /**
   * Returns the size of the embedded image data.
   * 
   * @return integer
   */
  public function getImageSize() { return $this->_imageSize; }
  
  /**
   * Returns the frame raw data.
   *
   * @return string
   */
  public function __toString()
  {
    $data = Transform::toUInt8($this->_encoding) . $this->_currency .
      $this->_price . "\0" . $this->_date . $this->_contact . "\0" .
      Transform::toUInt8($this->_delivery);
    switch ($this->_encoding) {
    case self::UTF16:
    case self::UTF16LE:
      $order = $this->_encoding == self::UTF16 ?
        Transform::MACHINE_ENDIAN_ORDER : Transform::LITTLE_ENDIAN_ORDER;
      $data .= Transform::toString16($this->_seller, $order) . "\0\0" .
        Transform::toString16($this->_description, $order) . "\0\0";
      break;
    case self::UTF16BE:
      $data .= Transform::toString16BE
        ($this->_seller . "\0\0" . $this->_description . "\0\0");
      break;
    default:
      $data .= $this->_seller . "\0" . $this->_description . "\0";
    }
    parent::setData
      ($data . ($this->_mimeType ?
                $this->_mimeType . "\0" . $this->_imageData : ""));
    return parent::__toString();
  }
}
