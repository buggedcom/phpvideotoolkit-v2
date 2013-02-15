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
 * @version    $Id: AdvancedMutualExclusion.php 108 2008-09-05 17:00:05Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>Advanced Mutual Exclusion Object</i> identifies streams that have a
 * mutual exclusion relationship to each other (in other words, only one of the
 * streams within such a relationship can be streamed—the rest are ignored).
 * There should be one instance of this object for each set of objects that
 * contain a mutual exclusion relationship. The exclusion type is used so that
 * implementations can allow user selection of common choices, such as language.
 * This object must be used if any of the streams in the mutual exclusion
 * relationship are hidden.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 108 $
 */
final class ASF_Object_AdvancedMutualExclusion extends ASF_Object
{
  const MUTEX_LANGUAGE = "d6e22a00-35da-11d1-9034-00a0c90349be";
  const MUTEX_BITRATE = "d6e22a01-35da-11d1-9034-00a0c90349be";
  const MUTEX_UNKNOWN = "d6e22a02-35da-11d1-9034-00a0c90349be";
  
  /** @var string */
  private $_exclusionType;
  
  /** @var Array */
  private $_streamNumbers = array();
  
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
    $streamNumbersCount = $this->_reader->readUInt16LE();
    for ($i = 0; $i < $streamNumbersCount; $i++)
      $this->_streamNumbers[] = $this->_reader->readUInt16LE();
  }
  
  /**
   * Returns the nature of the mutual exclusion relationship.
   *
   * @return string
   */
  public function getExclusionType() { return $this->_exclusionType; }
  
  /**
   * Returns an array of stream numbers.
   *
   * @return Array
   */
  public function getStreamNumbers() { return $this->_streamNumbers; }
}
