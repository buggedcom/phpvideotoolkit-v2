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
 * @version    $Id: STBL.php 92 2008-05-10 13:43:14Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Box.php");
/**#@-*/

/**
 * The <i>Sample Table Box</i> contains all the time and data indexing of the
 * media samples in a track. Using the tables here, it is possible to locate
 * samples in time, determine their type (e.g. I-frame or not), and determine
 * their size, container, and offset into that container.
 *
 * If the track that contains the Sample Table Box references no data, then the
 * Sample Table Box does not need to contain any sub-boxes (this is not a very
 * useful media track).
 *
 * If the track that the Sample Table Box is contained in does reference data,
 * then the following sub-boxes are required: {@link ISO14496_Box_STSD Sample
 * Description}, {@link ISO14496_Box_STSZ Sample Size},
 * {@link ISO14496_Box_STSC Sample To Chunk}, and {@link ISO14496_Box_STCO Chunk
 * Offset}. Further, the {@link ISO14496_Box_STSD Sample Description Box} shall
 * contain at least one entry. A Sample Description Box is required because it
 * contains the data reference index field which indicates which
 * {@link ISO14496_Box_DREF Data Reference Box} to use to retrieve the media
 * samples. Without the Sample Description, it is not possible to determine
 * where the media samples are stored. The {@link ISO14496_Box_STSS Sync Sample
 * Box} is optional. If the Sync Sample Box is not present, all samples are sync
 * samples.
 * 
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 92 $
 */
final class ISO14496_Box_STBL extends ISO14496_Box
{
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader = null, &$options = array())
  {
    parent::__construct($reader, $options);
    $this->setContainer(true);
    
    if ($reader === null)
      return;
    
    $this->constructBoxes();
  }
}
