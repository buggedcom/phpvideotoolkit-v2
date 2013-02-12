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
 * @version    $Id: SBGP.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * The <i>Sample To Group Box</i> table can be used to find the group that a
 * sample belongs to and the associated description of that sample group. The
 * table is compactly coded with each entry giving the index of the first sample
 * of a run of samples with the same sample group descriptor. The sample group
 * description ID is an index that refers to a {@link ISO14496_Box_SGPD Sample
 * Group Description Box}, which contains entries describing the characteristics
 * of each sample group.
 * 
 * There may be multiple instances of this box if there is more than one sample
 * grouping for the samples in a track. Each instance of the Sample To Group Box
 * has a type code that distinguishes different sample groupings. Within a
 * track, there shall be at most one instance of this box with a particular
 * grouping type. The associated Sample Group Description shall indicate the
 * same value for the grouping type.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_SBGP extends ISO14496_Box_Full
{
  /** @var integer */
  private $_groupingType;
  
  /** @var Array */
  private $_sampleToGroupTable = array();
  
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $groupingType = $this->_reader->readUInt32BE();
    $entryCount = $this->_reader->readUInt32BE();
    $data = $this->_reader->read
      ($this->getOffset() + $this->getSize() - $this->_reader->getOffset());
    for ($i = 1; $i <= $entryCount; $i++)
      $this->_sampleToGroupTable[$i] = array
        ("sampleCount" =>
           Transform::fromUInt32BE(substr($data, ($i - 1) * 8, 4)),
         "groupDescriptionIndex" =>
           Transform::fromUInt32BE(substr($data, $i * 8 - 4, 4)));
  }
  
  /**
   * Returns the grouping type that identifies the type (i.e. criterion used to
   * form the sample groups) of the sample grouping and links it to its sample
   * group description table with the same value for grouping type. At most one
   * occurrence of this box with the same value for groupingType shall exist for
   * a track.
   *
   * @return integer
   */
  public function getGroupingType()
  {
    return $this->_groupingType;
  }
  
  /**
   * Returns an array of values. Each entry is an array containing the following
   * keys.
   *   o sampleCount -- an integer that gives the number of consecutive samples
   *     with the same sample group descriptor. If the sum of the sample count
   *     in this box is less than the total sample count, then the reader should
   *     effectively extend it with an entry that associates the remaining
   *     samples with no group. It is an error for the total in this box to be
   *     greater than the sample_count documented elsewhere, and the reader
   *     behavior would then be undefined.
   *   o groupDescriptionIndex -- an integer that gives the index of the sample
   *     group entry which describes the samples in this group. The index ranges
   *     from 1 to the number of sample group entries in the
   *     {@link ISO14496_Box_SGPD Sample Group Description Box}, or takes the
   *     value 0 to indicate that this sample is a member of no group of this
   *     type.
   *
   * @return Array
   */
  public function getSampleToGroupTable()
  {
    return $this->_sampleToGroupTable;
  }
}
