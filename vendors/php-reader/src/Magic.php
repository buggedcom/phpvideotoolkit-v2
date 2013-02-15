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
 * @package   php-reader
 * @copyright Copyright (c) 2006-2008 PHP Reader Project Workgroup
 * @license   http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version   $Id: Magic.php 73 2008-04-12 19:07:31Z svollbehr $
 */

/**#@+ @ignore */
require_once("Reader.php");
/**#@-*/

/**
 * This class is used to classify the given file using some magic bytes
 * characteristic to a particular file type. The classification information can
 * be a MIME type or just text describing the file.
 *
 * This method is slower than determining the type by file suffix but on the
 * other hand reduces the risk of fail positives during the test.
 *
 * The magic file consists of ASCII characters defining the magic numbers for
 * different file types. Each row has 4 to 5 columns, empty and commented lines
 * (those starting with a hash character) are ignored. Columns are described
 * below.
 *
 *  o <b>1</b> -- byte number to begin checking from. ">" indicates a dependency
 *    upon the previous non-">" line
 *  o <b>2</b> -- type of data to match. Can be one of following
 *    - <i>byte</i> (single character)
 *    - <i>short</i> (machine-order 16-bit integer)
 *    - <i>long</i> (machine-order 32-bit integer)
 *    - <i>string</i> (arbitrary-length string)
 *    - <i>date</i> (long integer date (seconds since Unix epoch/1970))
 *    - <i>beshort</i> (big-endian 16-bit integer)
 *    - <i>belong</i> (big-endian 32-bit integer)
 *    - <i>bedate</i> (big-endian 32-bit integer date)
 *    - <i>leshort</i> (little-endian 16-bit integer)
 *    - <i>lelong</i> (little-endian 32-bit integer)
 *    - <i>ledate</i> (little-endian 32-bit integer date)
 *  o <b>3</b> -- contents of data to match
 *  o <b>4</b> -- file description/MIME type if matched
 *  o <b>5</b> -- optional MIME encoding if matched and if above was a MIME type
 *
 * @package   php-reader
 * @author    Sven Vollbehr <svollbehr@gmail.com>
 * @copyright Copyright (c) 2006-2008 PHP Reader Project Workgroup
 * @license   http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version   $Rev: 73 $
 */
final class Magic
{
  /** @var string */
  private $_magic;
  
  /**
   * Reads the magic information from given magic file.
   *
   * @param string $filename The path to the magic file.
   */
  public function __construct($filename)
  {
    $reader = new Reader($filename);
    $this->_magic = $reader->read($reader->getSize());
  }
  
  /**
   * Returns the recognized MIME type/description of the given file. The type
   * is determined by the content using magic bytes characteristic for the
   * particular file type.
   *
   * If the type could not be found, the function returns the default value, or
   * <var>false</var>.
   *
   * @param string $filename The file path whose type to determine.
   * @param string $default  The default value.
   * @return string|false
   */
  public function getType($filename, $default = false)
  {
    $reader = new Reader($filename);

    $parentOffset = 0;
    foreach (preg_split("/^/m", $this->_magic) as $line) {
      $chunks = array();
      if (!preg_match("/^(?P<Dependant>>?)(?P<Byte>\d+)\s+(?P<MatchType>\S+)" .
                      "\s+(?P<MatchData>\S+)(?:\s+(?P<MIMEType>[a-z]+\/[a-z-" .
                      "0-9]+)?(?:\s+(?P<Description>.+))?)?$/", $line, $chunks))
        continue;
      
      if ($chunks["Dependant"]) {
        $reader->setOffset($parentOffset);
        $reader->skip($chunks["Byte"]);
      } else
        $reader->setOffset($parentOffset = $chunks["Byte"]);

      $matchType = strtolower($chunks["MatchType"]);
      $matchData = preg_replace
        (array("/\\\\ /", "/\\\\\\\\/", "/\\\\([0-7]{1,3})/e",
               "/\\\\x([0-9A-Fa-f]{1,2})/e", "/0x([0-9A-Fa-f]+)/e"),
         array(" ", "\\\\", "pack(\"H*\", base_convert(\"$1\", 8, 16));",
               "pack(\"H*\", \"$1\");", "hexdec(\"$1\");"),
         $chunks["MatchData"]);

      switch ($matchType) {
      case "byte":    // single character
        $data = $reader->readInt8();
        break;
      case "short":   // machine-order 16-bit integer
        $data = $reader->readInt16();
        break;
      case "long":    // machine-order 32-bit integer
        $data = $reader->readInt32();
        break;
      case "string":  // arbitrary-length string
        $data = $reader->readString8(strlen($matchData));
        break;
      case "date":    // long integer date (seconds since Unix epoch/1970)
        $data = $reader->readInt64BE();
        break;
      case "beshort": // big-endian 16-bit integer
        $data = $reader->readUInt16BE();
        break;
      case "belong":  // big-endian 32-bit integer
      case "bedate":  // big-endian 32-bit integer date
        $data = $reader->readUInt32BE();
        break;
      case "leshort": // little-endian 16-bit integer
        $data = $reader->readUInt16LE();
        break;
      case "lelong":  // little-endian 32-bit integer
      case "ledate":  // little-endian 32-bit integer date
        $data = $reader->readUInt32LE();
        break;
      default:
        $data = null;
        break;
      }

      if (strcmp($data, $matchData) == 0) {
        if (!empty($chunks["MIMEType"]))
          return $chunks["MIMEType"];
        if (!empty($chunks["Description"]))
          return $chunks["Description"];
      }
    }
    return $default;
  }
}
