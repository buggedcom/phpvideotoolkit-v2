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
 * @subpackage ID3
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: ID3v2.php 107 2008-08-03 19:09:16Z svollbehr $
 */

/**#@+ @ignore */
require_once("Reader.php");
require_once("ID3/Exception.php");
require_once("ID3/Header.php");
require_once("ID3/ExtendedHeader.php");
require_once("ID3/Frame.php");
/**#@-*/

/**
 * This class represents a file containing ID3v2 headers as described in
 * {@link http://www.id3.org/id3v2.4.0-structure ID3v2 structure document}.
 *
 * ID3v2 is a general tagging format for audio, which makes it possible to store
 * meta data about the audio inside the audio file itself. The ID3 tag is mainly
 * targeted at files encoded with MPEG-1/2 layer I, MPEG-1/2 layer II, MPEG-1/2
 * layer III and MPEG-2.5, but may work with other types of encoded audio or as
 * a stand alone format for audio meta data.
 *
 * ID3v2 is designed to be as flexible and expandable as possible to meet new
 * meta information needs that might arise. To achieve that ID3v2 is constructed
 * as a container for several information blocks, called frames, whose format
 * need not be known to the software that encounters them. Each frame has an
 * unique and predefined identifier which allows software to skip unknown
 * frames.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @author     Ryan Butterfield <buttza@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 107 $
 */
final class ID3v2
{
  /** @var Reader */
  private $_reader;

  /** @var ID3_Header */
  private $_header;

  /** @var ID3_ExtendedHeader */
  private $_extendedHeader;

  /** @var ID3_Header */
  private $_footer;

  /** @var Array */
  private $_frames = array();

  /** @var string */
  private $_filename = false;

  /** @var Array */
  private $_options;

  /**
   * Constructs the ID3v2 class with given file and options. The options array
   * may also be given as the only parameter.
   *
   * The following options are currently recognized:
   *   o version -- The ID3v2 tag version to use in write operation. This option
   *     is automatically set when a tag is read from a file and defaults to
   *     version 4.0 for tag write.
   *   o readonly -- Indicates that the tag is read from a temporary file or
   *     another source it cannot be written back to. The tag can, however,
   *     still be written to another file.
   *
   * @todo  Only limited subset of flags are processed.
   * @todo  Utilize the SEEK frame and search for a footer to find the tag
   * @todo  Utilize the LINK frame to fetch frames from other sources
   * @param string|Reader $filename The path to the file, file descriptor of an
   *                                opened file, or {@link Reader} instance.
   * @param Array         $options  The options array.
   */
  public function __construct($filename = false, $options = array())
  {
    if (is_array($filename)) {
      $options = $filename;
      $filename = false;
    }

    $this->_options = &$options;
    if ($filename === false ||
        (is_string($filename) && file_exists($filename) === false) ||
        (is_resource($filename) && 
         in_array(get_resource_type($filename), array("file", "stream")))) {
      $this->_header = new ID3_Header(null, $options);
    } else {
      if (is_string($filename) && !isset($options["readonly"]))
        $this->_filename = $filename;
      if ($filename instanceof Reader)
        $this->_reader = &$filename;
      else
        $this->_reader = new Reader($filename);
      if ($this->_reader->readString8(3) != "ID3")
        throw new ID3_Exception("File does not contain ID3v2 tag");
      
      $startOffset = $this->_reader->getOffset();
      
      $this->_header = new ID3_Header($this->_reader, $options);
      if ($this->_header->getVersion() < 3 || $this->_header->getVersion() > 4)
        throw new ID3_Exception
          ("File does not contain ID3v2 tag of supported version");
      if ($this->_header->getVersion() < 4 &&
          $this->_header->hasFlag(ID3_Header::UNSYNCHRONISATION))
        throw new ID3_Exception
          ("Unsynchronisation not supported for this version of ID3v2 tag");
      unset($this->_options["unsyncronisation"]);
      if ($this->_header->hasFlag(ID3_Header::UNSYNCHRONISATION))
        $this->_options["unsyncronisation"] = true;
      if ($this->_header->hasFlag(ID3_Header::EXTENDEDHEADER))
        $this->_extendedHeader =
          new ID3_ExtendedHeader($this->_reader, $options);
      if ($this->_header->hasFlag(ID3_Header::FOOTER))
        $this->_footer = &$this->_header; // skip footer, and rather copy header

      while (true) {
        $offset = $this->_reader->getOffset();

        // Jump off the loop if we reached the end of the tag
        if ($offset - $startOffset - 10 >= $this->_header->getSize() -
            ($this->hasFooter() ? 10 : 0))
          break;

        // Jump off the loop if we reached the last frame
        if ($this->_reader->available() < 4 || Transform::fromUInt32BE
            ($identifier = $this->_reader->read(4)) == 0)
          break;
        $this->_reader->setOffset($offset);
        
        if (@fopen($filename = "ID3/Frame/" .
                   strtoupper($identifier) . ".php", "r", true) !== false)
          require_once($filename);
        if (class_exists($classname = "ID3_Frame_" . $identifier))
          $frame = new $classname($this->_reader, $options);
        else
          $frame = new ID3_Frame($this->_reader, $options);

        if (!isset($this->_frames[$frame->getIdentifier()]))
          $this->_frames[$frame->getIdentifier()] = array();
        $this->_frames[$frame->getIdentifier()][] = $frame;
      }
    }
  }

  /**
   * Returns the header object.
   *
   * @return ID3_Header
   */
  public function getHeader() { return $this->_header; }

  /**
   * Checks whether there is an extended header present in the tag. Returns
   * <var>true</var> if the header is present, <var>false</var> otherwise.
   *
   * @return boolean
   */
  public function hasExtendedHeader()
  {
    if ($this->_header)
      return $this->_header->hasFlag(ID3_Header::EXTENDEDHEADER);
  }

  /**
   * Returns the extended header object if present, or <var>false</var>
   * otherwise.
   *
   * @return ID3_ExtendedHeader|false
   */
  public function getExtendedHeader()
  {
    if ($this->hasExtendedHeader())
      return $this->_extendedHeader;
    return false;
  }

  /**
   * Sets the extended header object.
   *
   * @param ID3_ExtendedHeader $extendedHeader The header object
   */
  public function setExtendedHeader($extendedHeader)
  {
    if (is_subclass_of($extendedHeader, "ID3_ExtendedHeader")) {
      $this->_header->flags =
        $this->_header->flags | ID3_Header::EXTENDEDHEADER;
      $this->_extendedHeader->setOptions($this->_options);
      $this->_extendedHeader = $extendedHeader;
    } else throw new ID3_Exception("Invalid argument");
  }

  /**
   * Checks whether there is a frame given as an argument defined in the tag.
   * Returns <var>true</var> if one ore more frames are present,
   * <var>false</var> otherwise.
   *
   * @return boolean
   */
  public function hasFrame($identifier)
  {
    return isset($this->_frames[$identifier]);
  }

  /**
   * Returns all the frames the tag contains as an associate array. The frame
   * identifiers work as keys having an array of frames as associated value.
   *
   * @return Array
   */
  public function getFrames() { return $this->_frames; }

  /**
   * Returns an array of frames matching the given identifier or an empty array
   * if no frames matched the identifier.
   *
   * The identifier may contain wildcard characters "*" and "?". The asterisk
   * matches against zero or more characters, and the question mark matches any
   * single character.
   *
   * Please note that one may also use the shorthand $obj->identifier to access
   * the first frame with the identifier given. Wildcards cannot be used with
   * the shorthand.
   *
   * @return Array
   */
  public function getFramesByIdentifier($identifier)
  {
    $matches = array();
    $searchPattern = "/^" .
      str_replace(array("*", "?"), array(".*", "."), $identifier) . "$/i";
    foreach ($this->_frames as $identifier => $frames)
      if (preg_match($searchPattern, $identifier))
        foreach ($frames as $frame)
          $matches[] = $frame;
    return $matches;
  }

  /**
   * Adds a new frame to the tag and returns it.
   *
   * @param ID3_Frame $frame The frame to add.
   * @return ID3_Frame
   */
  public function addFrame($frame)
  {
    $frame->setOptions($this->_options);
    if (!$this->hasFrame($frame->getIdentifier()))
      $this->_frames[$frame->getIdentifier()] = array();
    return $this->_frames[$frame->getIdentifier()][] = $frame;
  }

  /**
   * Checks whether there is a footer present in the tag. Returns
   * <var>true</var> if the footer is present, <var>false</var> otherwise.
   *
   * @return boolean
   */
  public function hasFooter()
  {
    return $this->_header->hasFlag(ID3_Header::FOOTER);
  }

  /**
   * Returns the footer object if present, or <var>false</var> otherwise.
   *
   * @return ID3_Header|false
   */
  public function getFooter()
  {
    if ($this->hasFooter())
      return $this->_footer;
    return false;
  }

  /**
   * Sets whether the tag should have a footer defined.
   *
   * @param boolean $useFooter Whether the tag should have a footer
   */
  public function setFooter($useFooter)
  {
    if ($useFooter) {
      $this->_header->setFlags
        ($this->_header->getFlags() | ID3_Header::FOOTER);
      $this->_footer = &$this->_header;
    } else {
      /* Count footer bytes towards the tag size, so it gets removed or
         overridden upon re-write */
      if ($this->hasFooter())
        $this->_header->setSize($this->_header->getSize() + 10);

      $this->_header->setFlags
        ($this->_header->getFlags() & ~ID3_Header::FOOTER);
      $this->_footer = null;
    }
  }

  /**
   * Writes the possibly altered ID3v2 tag back to the file where it was read.
   * If the class was constructed without a file name, one can be provided here
   * as an argument. Regardless, the write operation will override previous
   * tag information, if found.
   *
   * If write is called without setting any frames to the tag, the tag is
   * removed from the file.
   *
   * @param string $filename The optional path to the file.
   */
  public function write($filename = false)
  {
    if ($filename === false && ($filename = $this->_filename) === false)
      throw new ID3_Exception("No file given to write the tag to");
    else if ($filename !== false && $this->_filename !== false &&
             realpath($filename) != realpath($this->_filename) &&
             !copy($this->_filename, $filename))
      throw new ID3_Exception("Unable to copy source to destination: " .
        realpath($this->_filename) . "->" . realpath($filename));

    if (($fd = fopen
         ($filename, file_exists($filename) ? "r+b" : "wb")) === false)
      throw new ID3_Exception("Unable to open file for writing: " . $filename);

    $oldTagSize = $this->_header->getSize();
    $tag = "" . $this;
    $tagSize = empty($this->_frames) ? 0 : strlen($tag);

    if ($this->_reader === null ||
        $tagSize - 10 > $oldTagSize || $tagSize == 0) {
      fseek($fd, 0, SEEK_END);
      $oldFileSize = ftell($fd);
      ftruncate($fd, $newFileSize = $tagSize - $oldTagSize + $oldFileSize);
      for ($i = 1, $cur = $oldFileSize; $cur > 0; $cur -= 1024, $i++) {
        fseek($fd, -(($i * 1024) + ($newFileSize - $oldFileSize)), SEEK_END);
        $buffer = fread($fd, 1024);
        fseek($fd, -($i * 1024), SEEK_END);
        fwrite($fd, $buffer, 1024);
      }
    }
    fseek($fd, 0);
    fwrite($fd, $tag, $tagSize);
    fclose($fd);

    $this->_filename = $filename;
  }

  /**
   * Magic function so that $obj->value will work. The method will attempt to
   * return the first frame that matches the identifier.
   *
   * If there is no frame or field with given name, the method will attempt to
   * create a frame with given identifier.
   *
   * If none of these work, an exception is thrown.
   *
   * @param string $name The frame or field name.
   * @return mixed
   */
  public function __get($name) {
    if (isset($this->_frames[strtoupper($name)]))
      return $this->_frames[strtoupper($name)][0];
    if (method_exists($this, "get" . ucfirst($name)))
      return call_user_func(array($this, "get" . ucfirst($name)));
    if (@fopen($filename =
               "ID3/Frame/" . strtoupper($name) . ".php", "r", true) !== false)
      require_once($filename);
    if (class_exists($classname = "ID3_Frame_" . strtoupper($name)))
      return $this->addFrame(new $classname());
    throw new ID3_Exception("Unknown frame/field: " . $name);
  }

  /**
   * Magic function so that isset($obj->value) will work. This method checks
   * whether the frame matching the identifier exists.
   *
   * @param string $name The frame identifier.
   * @return boolean
   */
  public function __isset($name)
  {
    return isset($this->_frames[strtoupper($name)]);
  }

  /**
   * Magic function so that unset($obj->value) will work. This method removes
   * all the frames matching the identifier.
   *
   * @param string $name The frame identifier.
   */
  public function __unset($name) { unset($this->_frames[strtoupper($name)]); }

  /**
   * Returns the tag raw data.
   *
   * @return string
   */
  public function __toString()
  {
    unset($this->_options["unsyncronisation"]);
    
    $data = "";
    foreach ($this->_frames as $frames)
      foreach ($frames as $frame)
        $data .= $frame;

    $datalen = strlen($data);
    $padlen = 0;
    
    if (isset($this->_options["unsyncronisation"]) &&
        $this->_options["unsyncronisation"] === true)
      $this->_header->setFlags
        ($this->_header->getFlags() | ID3_Header::UNSYNCHRONISATION);

    /* The tag padding is calculated as follows. If the tag can be written in
       the space of the previous tag, the remaining space is used for padding.
       If there is no previous tag or the new tag is bigger than the space taken
       by the previous tag, the padding is calculated using the following
       logaritmic equation: log(0.2(x + 10)), ranging from some 300 bytes to
       almost 5000 bytes given the tag length of 0..256M. */
    if ($this->hasFooter() === false) {
      if ($this->_reader !== null &&  $datalen < $this->_header->getSize())
        $padlen = $this->_header->getSize() - $datalen;
      else
        $padlen = ceil(log(0.2 * ($datalen / 1024 + 10), 10) * 1024);
    }

    /* ID3v2.4.0 CRC calculated w/ padding */
    if (!isset($this->_options["version"]) || $this->_options["version"] >= 4)
      $data = str_pad($data, $datalen + $padlen, "\0");

    if ($this->hasExtendedHeader()) {
      $this->_extendedHeader->setPadding($padlen);
      if ($this->_extendedHeader->hasFlag(ID3_ExtendedHeader::CRC32)) {
        $crc = crc32($data);
        if ($crc & 0x80000000)
          $crc = -(($crc ^ 0xffffffff) + 1);
        $this->_extendedHeader->setCrc($crc);
      }
      $data = $this->getExtendedHeader() . $data;
    }

    /* ID3v2.3.0 CRC calculated w/o padding */
    if (isset($this->_options["version"]) && $this->_options["version"] < 4)
      $data = str_pad($data, $datalen + $padlen, "\0");

    $this->_header->setSize(strlen($data));

    return "ID3" . $this->_header . $data .
      ($this->hasFooter() ? "3DI" . $this->getFooter() : "");
  }
}
