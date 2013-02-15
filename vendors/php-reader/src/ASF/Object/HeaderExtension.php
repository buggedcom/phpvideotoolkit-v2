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
 * @version    $Id: HeaderExtension.php 108 2008-09-05 17:00:05Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object/Container.php");
/**#@-*/

/**
 * The <i>Header Extension Object</i> allows additional functionality to be
 * added to an ASF file while maintaining backward compatibility. The Header
 * Extension Object is a container containing zero or more additional extended
 * header objects.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 108 $
 */
final class ASF_Object_HeaderExtension extends ASF_Object_Container
{
  const EXTENDED_STREAM_PROPERTIES = "14e6a5cb-c672-4332-8399-a96952065b5a";
  const ADVANCED_MUTUAL_EXCLUSION = "a08649cf-4775-4670-8a16-6e35357566cd";
  const GROUP_MUTUAL_EXCLUSION = "d1465a40-5a79-4338-b71b-e36b8fd6c249";
  const STREAM_PRIORITIZATION  = "d4fed15b-88d3-454f-81f0-ed5c45999e24";
  const BANDWIDTH_SHARING  = "a69609e6-517b-11d2-b6af-00c04fd908e9";
  const LANGUAGE_LIST  = "7c4346a9-efe0-4bfc-b229-393ede415c85";
  const METADATA  = "c5f8cbea-5baf-4877-8467-aa8c44fa4cca";
  const METADATA_LIBRARY = "44231c94-9498-49d1-a141-1d134e457054";
  const INDEX_PARAMETERS  = "d6e229df-35da-11d1-9034-00a0c90349be";
  const MEDIA_OBJECT_INDEX_PARAMETERS = "6b203bad-3f11-48e4-aca8-d7613de2cfa7";
  const TIMECODE_INDEX_PARAMETERS = "f55e496d-9797-4b5d-8c8b-604dfe9bfb24";
  const COMPATIBILITY = "75b22630-668e-11cf-a6d9-00aa0062ce6c";
  const ADVANCED_CONTENT_ENCRYPTION = "43058533-6981-49e6-9b74-ad12cb86d58c";
  const PADDING = "1806d474-cadf-4509-a4ba-9aabcb96aae8";

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
    
    $this->_reader->skip(22);
    $this->constructObjects
      (array
       (self::EXTENDED_STREAM_PROPERTIES => "ExtendedStreamProperties",
        self::ADVANCED_MUTUAL_EXCLUSION => "AdvancedMutualExclusion",
        self::GROUP_MUTUAL_EXCLUSION => "GroupMutualExclusion",
        self::STREAM_PRIORITIZATION  => "StreamPrioritization",
        self::BANDWIDTH_SHARING  => "BandwidthSharing",
        self::LANGUAGE_LIST  => "LanguageList",
        self::METADATA  => "Metadata",
        self::METADATA_LIBRARY => "MetadataLibrary",
        self::INDEX_PARAMETERS  => "IndexParameters",
        self::MEDIA_OBJECT_INDEX_PARAMETERS => "MediaObjectIndexParameters",
        self::TIMECODE_INDEX_PARAMETERS => "TimecodeIndexParameters",
        self::COMPATIBILITY => "Compatibility",
        self::ADVANCED_CONTENT_ENCRYPTION => "AdvancedContentEncryption",
        self::PADDING => "Padding"));
  }
}
