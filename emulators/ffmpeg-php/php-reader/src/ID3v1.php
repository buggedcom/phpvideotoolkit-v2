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
 * @version    $Id: ID3v1.php 107 2008-08-03 19:09:16Z svollbehr $
 */

/**#@+ @ignore */
require_once("Reader.php");
require_once("ID3/Exception.php");
/**#@-*/

/**
 * This class represents a file containing ID3v1 headers as described in
 * {@link http://www.id3.org/id3v2-00 The ID3-Tag Specification Appendix}.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @author     Ryan Butterfield <buttza@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 107 $
 */
final class ID3v1
{
  /** @var string */
  private $_title;

  /** @var string */
  private $_artist;

  /** @var string */
  private $_album;

  /** @var string */
  private $_year;

  /** @var string */
  private $_comment;

  /** @var integer */
  private $_track;

  /** @var integer */
  private $_genre = 255;

  /**
   * The genre list.
   *
   * @var Array
   */
  public static $genres = array
    ("Blues", "Classic Rock", "Country", "Dance", "Disco", "Funk", "Grunge",
     "Hip-Hop", "Jazz", "Metal", "New Age", "Oldies", "Other", "Pop", "R&B",
     "Rap", "Reggae", "Rock", "Techno", "Industrial", "Alternative", "Ska",
     "Death Metal", "Pranks", "Soundtrack", "Euro-Techno", "Ambient",
     "Trip-Hop", "Vocal", "Jazz+Funk", "Fusion", "Trance", "Classical",
     "Instrumental", "Acid", "House", "Game", "Sound Clip", "Gospel", "Noise",
     "AlternRock", "Bass", "Soul", "Punk", "Space", "Meditative",
     "Instrumental Pop", "Instrumental Rock", "Ethnic", "Gothic", "Darkwave",
     "Techno-Industrial", "Electronic", "Pop-Folk", "Eurodance", "Dream",
     "Southern Rock", "Comedy", "Cult", "Gangsta", "Top 40", "Christian Rap",
     "Pop/Funk", "Jungle", "Native American", "Cabaret", "New Wave",
     "Psychadelic", "Rave", "Showtunes", "Trailer", "Lo-Fi", "Tribal",
     "Acid Punk", "Acid Jazz", "Polka", "Retro", "Musical", "Rock & Roll",
     "Hard Rock", "Folk", "Folk-Rock", "National Folk", "Swing", "Fast Fusion",
     "Bebob", "Latin", "Revival", "Celtic", "Bluegrass", "Avantgarde",
     "Gothic Rock", "Progressive Rock", "Psychedelic Rock", "Symphonic Rock",
     "Slow Rock", "Big Band", "Chorus", "Easy Listening", "Acoustic", "Humour",
     "Speech", "Chanson", "Opera", "Chamber Music", "Sonata", "Symphony",
     "Booty Bass", "Primus", "Porn Groove", "Satire", "Slow Jam", "Club",
     "Tango", "Samba", "Folklore", "Ballad", "Power Ballad", "Rhythmic Soul",
     "Freestyle", "Duet", "Punk Rock", "Drum Solo", "A capella", "Euro-House",
     "Dance Hall", 255 => "Unknown");

  /** @var Reader */
  private $_reader;

  /** @var string */
  private $_filename = false;

  /**
   * Constructs the ID3v1 class with given file. The file is not mandatory
   * argument and may be omitted. A new tag can be written to a file also by
   * giving the filename to the {@link #write} method of this class.
   *
   * @param string|Reader $filename The path to the file, file descriptor of an
   *                                opened file, or {@link Reader} instance.
   */
  public function __construct($filename = false)
  {
    if ($filename instanceof Reader)
      $this->_reader = &$filename;
    else if ((is_string($filename) && ($this->_filename = $filename) !== false &&
              file_exists($filename) !== false) ||
             (is_resource($filename) &&
              in_array(get_resource_type($filename), array("file", "stream"))))
      $this->_reader = new Reader($filename);
    else
      return;

    if ($this->_reader->getSize() < 128)
      throw new ID3_Exception("File does not contain ID3v1 tag");
    $this->_reader->setOffset(-128);
    if ($this->_reader->read(3) != "TAG") {
      $this->_reader = false; // reset reader, see write
      throw new ID3_Exception("File does not contain ID3v1 tag");
    }

    $this->_title = rtrim($this->_reader->readString8(30), " \0");
    $this->_artist = rtrim($this->_reader->readString8(30), " \0");
    $this->_album = rtrim($this->_reader->readString8(30), " \0");
    $this->_year = $this->_reader->readString8(4);
    $this->_comment = rtrim($this->_reader->readString8(28), " \0");

    /* ID3v1.1 support for tracks */
    $v11_null = $this->_reader->read(1);
    $v11_track = $this->_reader->read(1);
    if (ord($v11_null) == 0 && ord($v11_track) != 0)
      $this->_track = ord($v11_track);
    else
      $this->_comment = rtrim($this->_comment . $v11_null . $v11_track, " \0");

    $this->_genre = $this->_reader->readInt8();
  }

  /**
   * Returns the title field.
   *
   * @return string
   */
  public function getTitle() { return $this->_title; }

  /**
   * Sets a new value for the title field. The field cannot exceed 30
   * characters in length.
   *
   * @param string $title The title.
   */
  public function setTitle($title) { $this->_title = $title; }

  /**
   * Returns the artist field.
   *
   * @return string
   */
  public function getArtist() { return $this->_artist; }

  /**
   * Sets a new value for the artist field. The field cannot exceed 30
   * characters in length.
   *
   * @param string $artist The artist.
   */
  public function setArtist($artist) { $this->_artist = $artist; }

  /**
   * Returns the album field.
   *
   * @return string
   */
  public function getAlbum() { return $this->_album; }

  /**
   * Sets a new value for the album field. The field cannot exceed 30
   * characters in length.
   *
   * @param string $album The album.
   */
  public function setAlbum($album) { $this->_album = $album; }

  /**
   * Returns the year field.
   *
   * @return string
   */
  public function getYear() { return $this->_year; }

  /**
   * Sets a new value for the year field. The field cannot exceed 4
   * characters in length.
   *
   * @param string $year The year.
   */
  public function setYear($year) { $this->_year = $year; }

  /**
   * Returns the comment field.
   *
   * @return string
   */
  public function getComment() { return $this->_comment; }

  /**
   * Sets a new value for the comment field. The field cannot exceed 30
   * characters in length.
   *
   * @param string $comment The comment.
   */
  public function setComment($comment) { $this->_comment = $comment; }

  /**
   * Returns the track field.
   *
   * @since ID3v1.1
   * @return integer
   */
  public function getTrack() { return $this->_track; }

  /**
   * Sets a new value for the track field. By setting this field you enforce the
   * 1.1 version to be used.
   *
   * @since ID3v1.1
   * @param integer $track The track number.
   */
  public function setTrack($track) { $this->_track = $track; }

  /**
   * Returns the genre.
   *
   * @return string
   */
  public function getGenre()
  {
    if (isset(self::$genres[$this->_genre]))
      return self::$genres[$this->_genre];
    else
      return self::$genres[255]; // unknown
  }

  /**
   * Sets a new value for the genre field. The value may either be a numerical
   * code representing one of the genres, or its string variant.
   *
   * The genre is set to unknown (code 255) in case the string is not found from
   * the static {@link $genres} array of this class.
   *
   * @param integer $genre The genre.
   */
  public function setGenre($genre)
  {
    if ((is_numeric($genre) && $genre >= 0 && $genre <= 255) ||
        ($genre = array_search($genre, self::$genres)) !== false)
      $this->_genre = $genre;
    else
      $this->_genre = 255; // unknown
  }

  /**
   * Writes the possibly altered ID3v1 tag back to the file where it was read.
   * If the class was constructed without a file name, one can be provided here
   * as an argument. Regardless, the write operation will override previous
   * tag information, if found.
   *
   * @param string $filename The optional path to the file.
   */
  public function write($filename = false)
  {
    if ($filename === false && ($filename = $this->_filename) === false)
      throw new ID3_Exception("No file given to write the tag to");

      if (($fd = fopen
           ($filename, file_exists($filename) ? "r+b" : "wb")) === false)
        throw new ID3_Exception("Unable to open file for writing: " . $filename);

    fseek($fd, $this->_reader !== false ? -128 : 0, SEEK_END);
    fwrite($fd, $this, 128);

    $this->_filename = $filename;
  }

  /**
   * Magic function so that $obj->value will work.
   *
   * @param string $name The field name.
   * @return mixed
   */
  public function __get($name)
  {
    if (method_exists($this, "get" . ucfirst(strtolower($name))))
      return call_user_func(array($this, "get" . ucfirst(strtolower($name))));
    else throw new ID3_Exception("Unknown field: " . $name);
  }

  /**
   * Magic function so that assignments with $obj->value will work.
   *
   * @param string $name  The field name.
   * @param string $value The field value.
   * @return mixed
   */
  public function __set($name, $value)
  {
    if (method_exists($this, "set" . ucfirst(strtolower($name))))
      call_user_func
        (array($this, "set" . ucfirst(strtolower($name))), $value);
    else throw new ID3_Exception("Unknown field: " . $name);
  }

  /**
   * Returns the tag raw data.
   *
   * @return string
   */
  private function __toString()
  {
    return "TAG" .
      Transform::toString8(substr($this->_title,  0, 30), 30) .
      Transform::toString8(substr($this->_artist, 0, 30), 30) .
      Transform::toString8(substr($this->_album,  0, 30), 30) .
      Transform::toString8(substr($this->_year,   0,  4),  4) .
      ($this->_track ?
       Transform::toString8(substr($this->_comment, 0, 28), 28) .
       "\0" . Transform::toInt8($this->_track) :
       Transform::toString8(substr($this->_comment, 0, 30), 30)) .
      Transform::toInt8($this->_genre);
  }
}
