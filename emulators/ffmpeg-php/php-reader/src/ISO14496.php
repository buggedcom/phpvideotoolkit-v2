<?php
/**
 * PHP Reader Library
 *
 * Copyright (c) 2008 The PHP Reader Project Workgroup. All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *   <li>Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *   <li>Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *   <li>Neither the name of the project workgroup nor the names of its
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
 * @copyright  Copyright (c) 2008 PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: ISO14496.php 101 2008-05-13 20:28:13Z svollbehr $
 */

/**#@+ @ignore */
require_once("Reader.php");
require_once("ISO14496/Box.php");
/**#@-*/

/**
 * This class represents a file in ISO base media file format as described in
 * ISO/IEC 14496 Part 12 standard.
 *
 * The ISO Base Media File Format is designed to contain timed media information
 * for a presentation in a flexible, extensible format that facilitates
 * interchange, management, editing, and presentation of the media. This
 * presentation may be local to the system containing the presentation, or may
 * be via a network or other stream delivery mechanism.
 *
 * The file structure is object-oriented; a file can be decomposed into
 * constituent objects very simply, and the structure of the objects inferred
 * directly from their type. The file format is designed to be independent of
 * any particular network protocol while enabling efficient support for them in
 * general.
 *
 * The ISO Base Media File Format is a base format for media file formats.
 *
 * 
 * An overall view of the normal encapsulation structure is provided in the
 * following table.
 *
 * The table shows those boxes that may occur at the top-level in the left-most
 * column; indentation is used to show possible containment. Thus, for example,
 * a {@link ISO14496_Box_TKHD Track Header Box} is found in a
 * {@link ISO14496_Box_TRAK Track Box}, which is found in a
 * {@link ISO14496_Box_MOOV Movie Box}. Not all boxes need be used in all files;
 * the mandatory boxes are marked with bold typeface. See the description of the
 * individual boxes for a discussion of what must be assumed if the optional
 * boxes are not present.
 *
 * User data objects shall be placed only in {@link ISO14496_Box_MOOV Movie} or
 * {@link ISO14496_Box_TRAK Track Boxes}, and objects using an extended type may
 * be placed in a wide variety of containers, not just the top level.
 *
 * <ul>
 * <li><b>ftyp</b> -- <i>{@link ISO14496_Box_FTYP File Type Box}</i>; file type
 *     and compatibility
 * <li>pdin -- <i>{@link ISO14496_Box_PDIN Progressive Download Information
 *     Box}</i>
 * <li><b>moov</b> -- <i>{@link ISO14496_Box_MOOV Movie Box}</i>; container for
 *     all the metadata
 *   <ul>
 *   <li><b>mvhd</b> -- <i>{@link ISO14496_Box_MVHD Movie Header Box}</i>;
 *       overall declarations
 *   <li><b>trak</b> -- <i>{@link ISO14496_Box_TRAK Track Box}</i>; container
 *       for an individual track or stream
 *     <ul>
 *     <li><b>tkhd</b> -- <i>{@link ISO14496_Box_TKHD Track Header Box}</i>;
 *         overall information about the track
 *     <li>tref -- <i>{@link ISO14496_Box_TREF Track Reference Box}</i>
 *     <li>edts -- <i>{@link ISO14496_Box_EDTS Edit Box}</i>
 *       <ul>
 *       <li>elst -- <i>{@link ISO14496_Box_ELST Edit List Box}</i>
 *       </ul>
 *     <li><b>mdia</b> -- <i>{@link ISO14496_Box_MDIA Media Box}</i>
 *       <ul>
 *       <li><b>mdhd</b> -- <i>{@link ISO14496_Box_MDHD Media Header Box}</i>;
 *           overall information about the media
 *       <li><b>hdlr</b> -- <i>{@link ISO14496_Box_HDLR Handler Reference
 *           Box}</i>; declares the media type
 *       <li><b>minf</b> -- <i>{@link ISO14496_Box_MINF Media Information
 *           Box}</i>
 *         <ul>
 *         <li>vmhd -- <i>{@link ISO14496_Box_VMHD Video Media Header Box}</i>;
 *             overall information (video track only)
 *         <li>smhd -- <i>{@link ISO14496_Box_SMHD Sound Media Header Box}</i>;
 *             overall information (sound track only)
 *         <li>hmhd -- <i>{@link ISO14496_Box_HMHD Hint Media Header Box}</i>;
 *             overall information (hint track only)
 *         <li>nmhd -- <i>{@link ISO14496_Box_NMHD Null Media Header Box}</i>;
 *             overall information (some tracks only)
 *         <li><b>dinf</b> -- <i>{@link ISO14496_Box_DINF Data Information
 *             Box}</i>
 *           <ul>
 *           <li><b>dref</b> -- <i>{@link ISO14496_Box_DREF Data Reference
 *               Box}</i>
 *           </ul>
 *         <li><b>stbl</b> -- <i>{@link ISO14496_Box_STBL Sample Table Box}</i>
 *           <ul>
 *           <li><b>stsd</b> -- <i>{@link ISO14496_Box_STSD Sample Descriptions
 *               Box}</i>
 *           <li><b>stts</b> -- <i>{@link ISO14496_Box_STTS Decoding Time To
 *               Sample Box}</i>
 *           <li>ctts -- <i>{@link ISO14496_Box_CTTS Composition Time To Sample
 *               Box}</i>
 *           <li><b>stsc</b> -- <i>{@link ISO14496_Box_STSC Sample To Chunk
 *               Box}</i>
 *           <li>stsz -- <i>{@link ISO14496_Box_STSZ Sample Size Box}</i>
 *           <li>stz2 -- <i>{@link ISO14496_Box_STZ2 Compact Sample Size
 *               Box}</i>
 *           <li><b>stco</b> -- <i>{@link ISO14496_Box_STCO Chunk Offset
 *               Box}</i>; 32-bit
 *           <li>co64 -- <i>{@link ISO14496_Box_CO64 Chunk Ooffset Box}</i>;
 *               64-bit
 *           <li>stss -- <i>{@link ISO14496_Box_STSS Sync Sample Table Box}</i>
 *           <li>stsh -- <i>{@link ISO14496_Box_STSH Shadow Sync Sample Table
 *               Box}</i>
 *           <li>padb -- <i>{@link ISO14496_Box_PADB Padding Bits Box}</i>
 *           <li>stdp -- <i>{@link ISO14496_Box_STDP Sample Degradation Priority
 *               Box}</i>
 *           <li>sdtp -- <i>{@link ISO14496_Box_SDTP Independent and Disposable
 *               Samples Box}</i>
 *           <li>sbgp -- <i>{@link ISO14496_Box_SBGP Sample To Group Box}</i>
 *           <li>sgpd -- <i>{@link ISO14496_Box_SGPD Sample Group
 *               Description}</i>
 *           <li>subs -- <i>{@link ISO14496_Box_SUBS Sub-Sample Information
 *               Box}</i>
 *           </ul>
 *         </ul>
 *       </ul>
 *     </ul>
 *   <li>mvex -- <i>{@link ISO14496_Box_MVEX Movie Extends Box}</i>
 *     <ul>
 *     <li>mehd -- <i>{@link ISO14496_Box_MEHD Movie Extends Header Box}</i>
 *     <li><b>trex</b> -- <i>{@link ISO14496_Box_TREX Track Extends Box}</i>
 *     </ul>
 *   <li>ipmc -- <i>{@link ISO14496_Box_IPMC IPMP Control Box}</i>
 *   </ul>
 * <li>moof -- <i>{@link ISO14496_Box_MOOF Movie Fragment Box}</i>
 *   <ul>
 *   <li><b>mfhd</b> -- <i>{@link ISO14496_Box_MFHD Movie Fragment Header
 *       Box}</i>
 *   <li>traf -- <i>{@link ISO14496_Box_TRAF Track Fragment Box}</i>
 *     <ul>
 *     <li><b>tfhd</b> -- <i>{@link ISO14496_Box_TFHD Track Fragment Header
 *         Box}</i>
 *     <li>trun -- <i>{@link ISO14496_Box_TRUN Track Fragment Run}</i>
 *     <li>sdtp -- <i>{@link ISO14496_Box_SDTP Independent and Disposable
 *         Samples}</i>
 *     <li>sbgp -- <i>{@link ISO14496_Box_SBGP !SampleToGroup Box}</i>
 *     <li>subs -- <i>{@link ISO14496_Box_SUBS Sub-Sample Information Box}</i>
 *     </ul>
 *   </ul>
 * <li>mfra -- <i>{@link ISO14496_Box_MFRA Movie Fragment Random Access Box}</i>
 *   <ul>
 *   <li>tfra -- <i>{@link ISO14496_Box_TFRA Track Fragment Random Access
 *       Box}</i>
 *   <li><b>mfro</b> -- <i>{@link ISO14496_Box_MFRO Movie Fragment Random Access
 *       Offset Box}</i>
 *   </ul>
 * <li>mdat -- <i>{@link ISO14496_Box_MDAT Media Data Box}</i>
 * <li>free -- <i>{@link ISO14496_Box_FREE Free Space Box}</i>
 * <li>skip -- <i>{@link ISO14496_Box_SKIP Free Space Box}</i>
 *   <ul>
 *   <li>udta -- <i>{@link ISO14496_Box_UDTA User Data Box}</i>
 *     <ul>
 *     <li>cprt -- <i>{@link ISO14496_Box_CPRT Copyright Box}</i>
 *     </ul>
 *   </ul>
 * <li>meta -- <i>{@link ISO14496_Box_META The Meta Box}</i>
 *   <ul>
 *   <li><b>hdlr</b> -- <i>{@link ISO14496_Box_HDLR Handler Reference Box}</i>;
 *       declares the metadata type
 *   <li>dinf -- <i>{@link ISO14496_Box_DINF Data Information Box}</i>
 *     <ul>
 *     <li>dref -- <i>{@link ISO14496_Box_DREF Data Reference Box}</i>; declares
 *         source(s) of metadata items
 *     </ul>
 *   <li>ipmc -- <i>{@link ISO14496_Box_IPMC IPMP Control Box}</i>
 *   <li>iloc -- <i>{@link ISO14496_Box_ILOC Item Location Box}</i>
 *   <li>ipro -- <i>{@link ISO14496_Box_IPRO Item Protection Box}</i>
 *     <ul>
 *     <li>sinf -- <i>{@link ISO14496_Box_SINF Protection Scheme Information
 *         Box}</i>
 *       <ul>
 *       <li>frma -- <i>{@link ISO14496_Box_FRMA Original Format Box}</i>
 *       <li>imif -- <i>{@link ISO14496_Box_IMIF IPMP Information Box}</i>
 *       <li>schm -- <i>{@link ISO14496_Box_SCHM Scheme Type Box}</i>
 *       <li>schi -- <i>{@link ISO14496_Box_SCHI Scheme Information Box}</i>
 *       </ul>
 *     </ul>
 *   <li>iinf -- <i>{@link ISO14496_Box_IINF Item Information Box}</i>
 *     <ul>
 *     <li>infe -- <i>{@link ISO14496_Box_INFE Item Information Entry Box}</i>
 *     </ul>
 *   <li>xml -- <i>{@link ISO14496_Box_XML XML Box}</i>
 *   <li>bxml -- <i>{@link ISO14496_Box_BXML Binary XML Box}</i>
 *   <li>pitm -- <i>{@link ISO14496_Box_PITM Primary Item Reference Box}</i>
 *   </ul>
 * </ul>
 *
 * There are two non-standard extensions to the ISO 14496 standard that add the
 * ability to include file meta information. Both the boxes reside under
 * moov.udta.meta.
 *
 * <ul>
 * <li><i>moov</i> -- <i>{@link ISO14496_Box_MOOV Movie Box}</i>; container for
 *     all the metadata
 * <li><i>udta</i> -- <i>{@link ISO14496_Box_UDTA User Data Box}</i>
 * <li><i>meta</i> -- <i>{@link ISO14496_Box_META The Meta Box}</i>
 *   <ul>
 *   <li>ilst -- <i>{@link ISO14496_Box_ILST The iTunes/iPod Tag Container
 *       Box}</i>
 *   <li>id32 -- <i>{@link ISO14496_Box_ID32 The ID3v2 Box}</i>
 *   </ul>
 * </ul>
 * 
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 101 $
 */
class ISO14496 extends ISO14496_Box
{
  /** @var string */
  private $_filename;
  
  /**
   * Constructs the ISO14496 class with given file and options.
   *
   * The following options are currently recognized:
   *   o base -- Indicates that only boxes with the given base path are parsed
   *     from the ISO base media file. Parsing all boxes can possibly have a
   *     significant impact on running time. Base path is a list of nested boxes
   *     separated by a dot.
   *   o readonly -- Indicates that the file is read from a temporary location
   *     or another source it cannot be written back to. The use of base option
   *     implies readonly option.
   *
   * @param string $filename The path to the file or file descriptor of an
   *                         opened file.
   * @param Array  $options  The options array.
   */
  public function __construct($filename, $options = array())
  {
    $this->_reader = new Reader($this->_filename = $filename);
    if (isset($options["base"]))
      $options["readonly"] = true;
    $this->setOptions($options);
    $this->setOffset(0);
    $this->setSize($this->_reader->getSize());
    $this->setType("file");
    $this->setContainer(true);
    $this->constructBoxes();
  }
  
  /**
   * Writes the changes back to the original media file.
   *
   * Please note: currently the method writes only ID32 and ILST boxes to
   * <i>moov.udta.meta</i>. Changes to any other box are discarded. Write
   * operation will overwrite <i>moov.udta</i>, if found.
   */
  public function write()
  {
    if (!isset($this->moov->udta->meta->ilst) &&
        !isset($this->moov->udta->meta->id32))
      throw new ISO14496_Exception("Nothing to write");

    if ($this->getOption("readonly", false) !== false)
      throw new ISO14496_Exception("File is read only");
    
    if (($fd = fopen($this->_filename, file_exists
                     ($this->_filename) ? "r+b" : "wb")) === false)
      throw new ISO14496_Exception
        ("Unable to open file for writing: " . $filename);
    
    $this->moov->udta->meta->hdlr->setHandlerType("mdir");
    
    /* Calculate start position */
    $mark = ($this->moov->udta->getOffset() > 0 ?
             $this->moov->udta->getOffset() :
             $this->moov->getOffset() + $this->moov->getSize());
    
    /* Calculate file size */
    fseek($fd, 0, SEEK_END);
    $oldFileSize = ftell($fd);
    $newFileSize = $oldFileSize -
      ($this->moov->udta->getOffset() > 0 ? $this->moov->udta->getSize() : 0) -
      (isset($this->moov->udta->meta->free) ?
       $this->moov->udta->meta->free->getSize() : 0) +
      strlen($this->moov->udta);

    /* Calculate free space size */
    if ($oldFileSize < $newFileSize) {
      // Add free space to the file calculated using the following logaritmic
      // equation: log(0.2(x + 10)), ranging from 1k to 9k given the file size
      // of 0..4G
      $this->moov->udta->meta->free->setSize
        (ceil(log(0.2 * ($newFileSize / 1024 + 10), 10) * 1024));
      ftruncate($fd, $newFileSize += $this->moov->udta->meta->free->getSize());
      
      // Move data to the end of the file
      for ($i = 1, $cur = $oldFileSize; $cur > $mark; $cur -= 1024, $i++) {
        fseek($fd, -(($i * 1024) +
              ($excess = $cur - 1024 > $mark ? 0 : $cur - $mark - 1024) +
              ($newFileSize - $oldFileSize)), SEEK_END);
        $buffer = fread($fd, 1024);
        fseek($fd, -(($i * 1024) + $excess), SEEK_END);
        fwrite($fd, $buffer, 1024);
      }
      
      // Update stco/co64 to correspond the data move
      foreach ($this->moov->getBoxesByIdentifier("trak") as $trak) {
        $chunkOffsetBox = 
          (isset($trak->mdia->minf->stbl->stco) ?
           $trak->mdia->minf->stbl->stco : $trak->mdia->minf->stbl->co64);
        $chunkOffsetTable = $chunkOffsetBox->getChunkOffsetTable();
        $chunkOffsetTableCount = count($chunkOffsetTable);
        $chunkOffsetDelta = $newFileSize - $oldFileSize;
        for ($i = 1; $i <= $chunkOffsetTableCount; $i++)
          $chunkOffsetTable[$i] += $chunkOffsetDelta;
        $chunkOffsetBox->setChunkOffsetTable($chunkOffsetTable);
        fseek($fd, $chunkOffsetBox->getOffset());
        fwrite($fd, $chunkOffsetBox, $chunkOffsetBox->getSize());
      }
    } 
    else
      $this->moov->udta->meta->free->setSize($oldFileSize - $newFileSize);
    
    /* Update the target box */
    fseek($fd, $mark);
    $this->moov->udta->setSize(fwrite($fd, $this->moov->udta));
    
    /* Update the parent box */
    fseek($fd, $this->moov->getOffset());
    fwrite($fd, Transform::toUInt32BE($this->moov->getSize()));
    
    fclose($fd);
  }
  
  /**
   * Returns the raw data of the ISO14496 file.
   *
   * @return string
   */
  public function __toString($data = "")
  {
    if ($this->isContainer())
      foreach ($this->getBoxes() as $name => $boxes)
        foreach ($boxes as $box)
          $data .= $box;
    return $data;
  }
}
