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
 * @version    $Id: SDTP.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * The <i>Independent and Disposable Samples Box</i> optional table answers
 * three questions about sample dependency:
 *   1) does this sample depend on others (is it an I-picture)?
 *   2) do no other samples depend on this one?
 *   3) does this sample contain multiple (redundant) encodings of the data at
 *      this time-instant (possibly with different dependencies)?
 *
 * In the absence of this table:
 *   1) the sync sample table answers the first question; in most video codecs,
 *      I-pictures are also sync points,
 *   2) the dependency of other samples on this one is unknown.
 *   3) the existence of redundant coding is unknown.
 *
 * When performing trick modes, such as fast-forward, it is possible to use the
 * first piece of information to locate independently decodable samples.
 * Similarly, when performing random access, it may be necessary to locate the
 * previous sync point or random access recovery point, and roll-forward from
 * the sync point or the pre-roll starting point of the random access recovery
 * point to the desired point. While rolling forward, samples on which no others
 * depend need not be retrieved or decoded.
 * 
 * The value of sampleIsDependedOn is independent of the existence of redundant
 * codings. However, a redundant coding may have different dependencies from the
 * primary coding; if redundant codings are available, the value of
 * sampleDependsOn documents only the primary coding.
 *
 * A sample dependency Box may also occur in the {@link ISO14496_Box_TRAF Track
 * Fragment Box}.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_SDTP extends ISO14496_Box_Full
{
  /** @var Array */
  private $_sampleDependencyTypeTable = array();
  
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $data = $this->_reader->read
      ($this->getOffset() + $this->getSize() - $this->_reader->getOffset());
    $dataSize = strlen($data);
    for ($i = 1; $i <= $dataSize; $i++)
      $this->_sampleDependencyTypeTable[$i] = array
        ("sampleDependsOn" => (($tmp = Transform::fromInt8
                                ($data[$i - 1])) >> 4) & 0x3,
         "sampleIsDependedOn" => ($tmp >> 2) & 0x3,
         "sampleHasRedundancy" => $tmp & 0x3);
  }
  
  /**
   * Returns an array of values. Each entry is an array containing the following
   * keys.
   *   o sampleDependsOn -- takes one of the following four values:
   *     0: the dependency of this sample is unknown;
   *     1: this sample does depend on others (not an I picture);
   *     2: this sample does not depend on others (I picture);
   *     3: reserved
   *   o sampleIsDependedOn -- takes one of the following four values:
   *     0: the dependency of other samples on this sample is unknown;
   *     1: other samples depend on this one (not disposable);
   *     2: no other sample depends on this one (disposable);
   *     3: reserved
   *   o sampleHasRedundancy -- takes one of the following four values:
   *     0: it is unknown whether there is redundant coding in this sample;
   *     1: there is redundant coding in this sample;
   *     2: there is no redundant coding in this sample;
   *     3: reserved
   *
   * @return Array
   */
  public function getSampleDependencyTypeTable()
  {
    return $this->_sampleDependencyTypeTable;
  }
}
