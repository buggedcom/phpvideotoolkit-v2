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
 * @version    $Id: ContentBranding.php 108 2008-09-05 17:00:05Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>Content Branding Object</i> stores branding data for an ASF file,
 * including information about a banner image and copyright associated with the
 * file.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 108 $
 */
final class ASF_Object_ContentBranding extends ASF_Object
{
  /** Indicates that there is no banner */
  const TYPE_NONE = 0;
  
  /** Indicates that the data represents a bitmap */
  const TYPE_BMP = 1;
  
  /** Indicates that the data represents a JPEG */
  const TYPE_JPEG = 2;
  
  /** Indicates that the data represents a GIF */
  const TYPE_GIF = 3;
  
  
  /** @var integer */
  private $_bannerImageType;
  
  /** @var string */
  private $_bannerImageData;
  
  /** @var string */
  private $_bannerImageUrl;
  
  /** @var string */
  private $_copyrightUrl;
  
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
    
    $this->_bannerImageType = $this->_reader->readUInt32LE();
    $bannerImageDataSize = $this->_reader->readUInt32LE();
    $this->_bannerImageData = $this->_reader->read($bannerImageDataSize);
    $bannerImageUrlLength = $this->_reader->readUInt32LE();
    $this->_bannerImageUrl = $this->_reader->read($bannerImageUrlLength);
    $copyrightUrlLength = $this->_reader->readUInt32LE();
    $this->_copyrightUrl = $this->_reader->read($copyrightUrlLength);
  }
  
  /**
   * Returns the type of data contained in the <i>Banner Image Data</i>. Valid
   * values are 0 to indicate that there is no banner image data; 1 to indicate
   * that the data represent a bitmap; 2 to indicate that the data represents a
   * JPEG; and 3 to indicate that the data represents a GIF. If this value is
   * set to 0, then the <i>Banner Image Data Size field is set to 0, and the
   * <i>Banner Image Data</i> field is empty.
   *
   * @return integer
   */
  public function getBannerImageType() { return $this->_bannerImageType; }
  
  /**
   * Returns the entire banner image, including the header for the appropriate
   * image format.
   *
   * @return string
   */
  public function getBannerImageData() { return $this->_bannerImageData; }
  
  /**
   * Returns, if present, a link to more information about the banner image.
   *
   * @return string
   */
  public function getBannerImageUrl() { return $this->_bannerImageUrl; }
  
  /**
   * Returns, if present, a link to more information about the copyright for the
   * content.
   *
   * @return string
   */
  public function getCopyrightUrl() { return $this->_copyrightUrl; }
}
