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
 * @version    $Id: STZ2.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * The <i>Sample Size Box</i> contains the sample count and a table giving the
 * size in bytes of each sample. This allows the media data itself to be
 * unframed. The total number of samples in the media is always indicated in the
 * sample count.
 *
 * There are two variants of the sample size box. This variant permits smaller
 * than 32-bit size fields, to save space when the sizes are varying but small.
 * One of the boxes must be present; the {@link ISO14496_Box_STSZ another
 * variant} is preferred for maximum compatibility.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_STZ2 extends ISO14496_Box_Full
{
  /** @var Array */
  private $_sampleSizeTable = array();
  
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $this->_reader->skip(3);
    $fieldSize = $this->_reader->readInt8();
    $sampleCount = $this->_reader->readUInt32BE();
    $data = $this->_reader->read
      ($this->getOffset() + $this->getSize() - $this->_reader->getOffset());
    for ($i = 1; $i <= $sampleCount; $i++) {
      switch ($fieldSize) {
      case 4:
        $this->_sampleSizeTable[$i] =
          (($tmp = Transform::fromInt8($data[$i - 1])) >> 4) & 0xf;
        if ($i + 1 < $sampleCount)
          $this->_sampleSizeTable[$i++] = $tmp & 0xf;
        break;
      case 8:
        $this->_sampleSizeTable[$i] = Transform::fromInt8($data[$i - 1]);
        break;
      case 16:
        $this->_sampleSizeTable[$i] =
          Transform::fromUInt16BE(substr($data, ($i - 1) * 2, 2));
        break;
      }
    }
  }
  
  /**
   * Returns an array of sample sizes specifying the size of a sample, indexed
   * by its number.
   *
   * @return Array
   */
  public function getSampleSizeTable()
  {
    return $this->_sampleSizeTable;
  }
}
