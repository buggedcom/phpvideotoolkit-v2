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
 * @version    $Id: SUBS.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * The <i>Sub-Sample Information Box</i> is designed to contain sub-sample
 * information.
 *
 * A sub-sample is a contiguous range of bytes of a sample. The specific
 * definition of a sub-sample shall be supplied for a given coding system (e.g.
 * for ISO/IEC 14496-10, Advanced Video Coding). In the absence of such a
 * specific definition, this box shall not be applied to samples using that
 * coding system.
 *
 * If subsample_count is 0 for any entry, then those samples have no subsample
 * information and no array follows. The table is sparsely coded; the table
 * identifies which samples have sub-sample structure by recording the
 * difference in sample-number between each entry. The first entry in the table
 * records the sample number of the first sample having sub-sample information.
 *
 * Note: It is possible to combine subsamplePriority and discardable such that
 * when subsamplePriority is smaller than a certain value, discardable is set to
 * 1. However, since different systems may use different scales of priority
 * values, to separate them is safe to have a clean solution for discardable
 * sub-samples.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_SUBS extends ISO14496_Box_Full
{
  /** @var Array */
  private $_subSampleTable = array();
  
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $entryCount = $this->_reader->readUInt32BE();
    for ($i = 0; $i < $entryCount; $i++) {
      $entry = array();
      $entry["sampleDelta"] = $this->_reader->readUInt32BE();
      $entry["subsamples"] = array();
      if (($subsampleCount = $this->_reader->readUInt16BE()) > 0) {
        for ($j = 0; $j < $subsampleCount; $j++) {
          $subsample = array();
          if ($this->getVersion() == 1)
            $subsample["subsampleSize"] = $this->_reader->readUInt32BE();
          else
            $subsample["subsampleSize"] = $this->_reader->readUInt16BE();
          $subsample["subsamplePriority"] = $this->_reader->readInt8();
          $subsample["discardable"] = $this->_reader->readInt8();
          $this->_reader->skip(4);
          $entry["subsamples"][] = $subsample;
        }
        $this->_subSampleTable[] = $entry;
      }
    }
  }
  
  /**
   * Returns an array of values. Each entry is an array containing the following
   * keys.
   *   o sampleDelta -- an integer that specifies the sample number of the
   *     sample having sub-sample structure. It is coded as the difference
   *     between the desired sample number, and the sample number indicated in
   *     the previous entry. If the current entry is the first entry, the value
   *     indicates the sample number of the first sample having sub-sample
   *     information, that is, the value is the difference between the sample
   *     number and zero (0).
   *   o subsamples -- an array of subsample arrays, each containing the
   *     following keys.
   *       o subsampleSize -- an integer that specifies the size, in bytes, of
   *         the current sub-sample.
   *       o subsamplePriority -- an integer specifying the degradation priority
   *         for each sub-sample. Higher values of subsamplePriority, indicate
   *         sub-samples which are important to, and have a greater impact on,
   *         the decoded quality.
   *       o discardable -- equal to 0 means that the sub-sample is required to
   *         decode the current sample, while equal to 1 means the sub-sample is
   *         not required to decode the current sample but may be used for
   *         enhancements, e.g., the sub-sample consists of supplemental
   *         enhancement information (SEI) messages.
   *
   * @return Array
   */
  public function getSubSampleTable()
  {
    return $this->_subSampleTable;
  }
}
