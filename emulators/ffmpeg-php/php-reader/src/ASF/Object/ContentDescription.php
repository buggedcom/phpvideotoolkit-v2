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
 * @version    $Id: ContentDescription.php 102 2008-06-23 20:41:20Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>Content Description Object</i> lets authors record well-known data
 * describing the file and its contents. This object is used to store standard
 * bibliographic information such as title, author, copyright, description, and
 * rating information. This information is pertinent to the entire file.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2006-2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 102 $
 */
final class ASF_Object_ContentDescription extends ASF_Object
{
  /** @var string */
  private $_title;

  /** @var string */
  private $_author;

  /** @var string */
  private $_copyright;

  /** @var string */
  private $_description;

  /** @var string */
  private $_rating;

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

    $titleLen = $this->_reader->readUInt16LE();
    $authorLen = $this->_reader->readUInt16LE();
    $copyrightLen = $this->_reader->readUInt16LE();
    $descriptionLen = $this->_reader->readUInt16LE();
    $ratingLen = $this->_reader->readUInt16LE();

    $this->_title = iconv
      ("utf-16le", $this->getOption("encoding"),
       $this->_reader->readString16LE($titleLen));
    $this->_author =  iconv
      ("utf-16le", $this->getOption("encoding"),
       $this->_reader->readString16LE($authorLen));
    $this->_copyright =  iconv
      ("utf-16le", $this->getOption("encoding"),
       $this->_reader->readString16LE($copyrightLen));
    $this->_description =  iconv
      ("utf-16le", $this->getOption("encoding"),
       $this->_reader->readString16LE($descriptionLen));
    $this->_rating =  iconv
      ("utf-16le", $this->getOption("encoding"),
       $this->_reader->readString16LE($ratingLen));
  }

  /**
   * Returns the title information.
   *
   * @return string
   */
  public function getTitle() { return $this->_title; }

  /**
   * Returns the author information.
   *
   * @return string
   */
  public function getAuthor() { return $this->_author; }

  /**
   * Returns the copyright information.
   *
   * @return string
   */
  public function getCopyright() { return $this->_copyright; }

  /**
   * Returns the description information.
   *
   * @return string
   */
  public function getDescription() { return $this->_description; }

  /**
   * Returns the rating information.
   *
   * @return string
   */
  public function getRating() { return $this->_rating; }
}
