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
 * @version    $Id: TMED.php 65 2008-04-02 15:22:46Z svollbehr $
 */

/**#@+ @ignore */
require_once("ID3/Frame/AbstractText.php");
/**#@-*/

/**
 * The <i>Media type</i> frame describes from which media the sound originated.
 * This may be a text string or a reference to the predefined media types found
 * in the list below. Example: "VID/PAL/VHS" $00.
 *
 * <pre>
 *  DIG    Other digital media
 *    /A    Analogue transfer from media
 *
 *  ANA    Other analogue media
 *    /WAC  Wax cylinder
 *    /8CA  8-track tape cassette
 *
 *  CD     CD
 *    /A    Analogue transfer from media
 *    /DD   DDD
 *    /AD   ADD
 *    /AA   AAD
 *
 *  LD     Laserdisc
 *
 *  TT     Turntable records
 *    /33    33.33 rpm
 *    /45    45 rpm
 *    /71    71.29 rpm
 *    /76    76.59 rpm
 *    /78    78.26 rpm
 *    /80    80 rpm
 *
 *  MD     MiniDisc
 *    /A    Analogue transfer from media
 *
 *  DAT    DAT
 *    /A    Analogue transfer from media
 *    /1    standard, 48 kHz/16 bits, linear
 *    /2    mode 2, 32 kHz/16 bits, linear
 *    /3    mode 3, 32 kHz/12 bits, non-linear, low speed
 *    /4    mode 4, 32 kHz/12 bits, 4 channels
 *    /5    mode 5, 44.1 kHz/16 bits, linear
 *    /6    mode 6, 44.1 kHz/16 bits, 'wide track' play
 *
 *  DCC    DCC
 *    /A    Analogue transfer from media
 *
 *  DVD    DVD
 *    /A    Analogue transfer from media
 *
 *  TV     Television
 *    /PAL    PAL
 *    /NTSC   NTSC
 *    /SECAM  SECAM
 *
 *  VID    Video
 *    /PAL    PAL
 *    /NTSC   NTSC
 *    /SECAM  SECAM
 *    /VHS    VHS
 *    /SVHS   S-VHS
 *    /BETA   BETAMAX
 *
 *  RAD    Radio
 *    /FM   FM
 *    /AM   AM
 *    /LW   LW
 *    /MW   MW
 *
 *  TEL    Telephone
 *    /I    ISDN
 *
 *  MC     MC (normal cassette)
 *    /4    4.75 cm/s (normal speed for a two sided cassette)
 *    /9    9.5 cm/s
 *    /I    Type I cassette (ferric/normal)
 *    /II   Type II cassette (chrome)
 *    /III  Type III cassette (ferric chrome)
 *    /IV   Type IV cassette (metal)
 *
 *  REE    Reel
 *    /9    9.5 cm/s
 *    /19   19 cm/s
 *    /38   38 cm/s
 *    /76   76 cm/s
 *    /I    Type I cassette (ferric/normal)
 *    /II   Type II cassette (chrome)
 *    /III  Type III cassette (ferric chrome)
 *    /IV   Type IV cassette (metal)
 * </pre>
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 65 $
 */
final class ID3_Frame_TMED extends ID3_Frame_AbstractText {}
