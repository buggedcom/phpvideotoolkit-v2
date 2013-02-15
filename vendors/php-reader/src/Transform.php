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
 * @copyright Copyright (c) 2006-2008 The PHP Reader Project Workgroup
 * @license   http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version   $Id: Transform.php 105 2008-07-30 14:56:47Z svollbehr $
 */

/**
 * An utility class to perform simple byte transformations on data.
 *
 * @package   php-reader
 * @author    Sven Vollbehr <svollbehr@gmail.com>
 * @author    Ryan Butterfield <buttza@gmail.com>
 * @copyright Copyright (c) 2006-2008 The PHP Reader Project Workgroup
 * @license   http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version   $Rev: 105 $
 * @static
 */
final class Transform
{
  const MACHINE_ENDIAN_ORDER = 0;
  const LITTLE_ENDIAN_ORDER  = 1;
  const BIG_ENDIAN_ORDER     = 2;

  /**
   * Default private constructor for a static class.
   */
  private function __construct() {}
  
  /**
   * Returns whether the current machine endian order is little endian.
   *
   * @return boolean
   */
  public static function isLittleEndian()
  {
    return self::fromInt32("\x01\x00\x00\x00") == 1;
  }
  
  /**
   * Returns whether the current machine endian order is big endian.
   *
   * @return boolean
   */
  public static function isBigEndian()
  {
    return self::fromInt32("\x00\x00\x00\x01") == 1;
  }
  
  /**
   * Returns 64-bit float as little-endian ordered binary data string.
   *
   * @param  integer $value The input value.
   * @return string
   */
  public static function toInt64LE($value)
  {
    return pack("V*", $value & 0xffffffff, $value / (0xffffffff+1));
  }

  /**
   * Returns little-endian ordered binary data as 64-bit float. PHP does not
   * support 64-bit integers as the long integer is of 32-bits but using
   * aritmetic operations it is implicitly converted into floating point which
   * is of 64-bits long.
   *
   * @param string $value The binary data string.
   * @return integer
   */
  public static function fromInt64LE($value)
  {
    list(, $lolo, $lohi, $hilo, $hihi) = unpack("v*", $value);
    return ($hihi * (0xffff+1) + $hilo) * (0xffffffff+1) +
      ($lohi * (0xffff+1) + $lolo);
  }

  /**
   * Returns 64-bit float as big-endian ordered binary data string.
   *
   * @param integer $value The input value.
   * @return string
   */
  public static function toInt64BE($value)
  {
    return pack("N*", $value / (0xffffffff+1), $value & 0xffffffff);
  }

  /**
   * Returns big-endian ordered binary data as 64-bit float. PHP does not
   * support 64-bit integers as the long integer is of 32-bits but using
   * aritmetic operations it is implicitly converted into floating point which
   * is of 64-bits long.
   *
   * @param string $value The binary data string.
   * @return integer
   */
  public static function fromInt64BE($value)
  {
    list(, $hihi, $hilo, $lohi, $lolo) = unpack("n*", $value);
    return ($hihi * (0xffff+1) + $hilo) * (0xffffffff+1) +
      ($lohi * (0xffff+1) + $lolo);
  }

  /**
   * Returns signed 32-bit integer as machine-endian ordered binary data.
   *
   * @param integer $value The input value.
   * @return string
   */
  public static function toInt32($value)
  {
    return pack("l*", $value);
  }

  /**
   * Returns machine-endian ordered binary data as signed 32-bit integer.
   *
   * @param string $value The binary data string.
   * @return integer
   */
  public static function fromInt32($value)
  {
    list(, $int) = unpack("l*", $value);
    return $int;
  }

  /**
   * Returns signed 32-bit integer as little-endian ordered binary data.
   *
   * @param integer $value The input value.
   * @return string
   */
  public static function toInt32LE($value)
  {
    if (self::isBigEndian())
      return strrev(self::toInt32($value));
    else
      return self::toInt32($value);
  }

  /**
   * Returns little-endian ordered binary data as signed 32-bit integer.
   *
   * @param string $value The binary data string.
   * @return integer
   */
  public static function fromInt32LE($value)
  {
    if (self::isBigEndian())
      return self::fromInt32(strrev($value));
    else
      return self::fromInt32($value);
  }

  /**
   * Returns signed 32-bit integer as big-endian ordered binary data.
   *
   * @param integer $value The input value.
   * @return string
   */
  public static function toInt32BE($value)
  {
    if (self::isBigEndian())
      return self::toInt32($value);
    else
      return strrev(self::toInt32($value));
  }

  /**
   * Returns big-endian ordered binary data as signed 32-bit integer.
   *
   * @param string $value The binary data string.
   * @return integer
   */
  public static function fromInt32BE($value)
  {
    if (self::isBigEndian())
      return self::fromInt32($value);
    else
      return self::fromInt32(strrev($value));
  }

  /**
   * Returns unsigned 32-bit integer as little-endian ordered binary data.
   *
   * @param integer $value The input value.
   * @return string
   */
  public static function toUInt32LE($value)
  {
    return pack("V*", $value);
  }

  /**
   * Returns little-endian ordered binary data as unsigned 32-bit integer.
   *
   * @param string $value The binary data string.
   * @return integer
   */
  public static function fromUInt32LE($value)
  {
    if (PHP_INT_SIZE < 8) {
      list(, $lo, $hi) = unpack("v*", $value);
      return $hi * (0xffff+1) + $lo; // eq $hi << 16 | $lo
    } else {
      list(, $int) = unpack("V*", $value);
      return $int;
    }
  }

  /**
   * Returns unsigned 32-bit integer as big-endian ordered binary data.
   *
   * @param integer $value The input value.
   * @return string
   */
  public static function toUInt32BE($value)
  {
    return pack("N*", $value);
  }

  /**
   * Returns big-endian ordered binary data as unsigned 32-bit integer.
   *
   * @param string $value The binary data string.
   * @return integer
   */
  public static function fromUInt32BE($value)
  {
    if (PHP_INT_SIZE < 8) {
      list(, $hi, $lo) = unpack("n*", $value);
      return $hi * (0xffff+1) + $lo; // eq $hi << 16 | $lo
    } else {
      list(, $int) = unpack("N*", $value);
      return $int;
    }
  }

  /**
   * Returns signed 16-bit integer as machine endian ordered binary data.
   *
   * @param integer $value The input value.
   * @return string
   */
  public static function toInt16($value)
  {
    return pack("s*", $value);
  }

  /**
   * Returns machine endian ordered binary data as signed 16-bit integer.
   *
   * @param string $value The binary data string.
   * @return integer
   */
  public static function fromInt16($value)
  {
    list(, $int) = unpack("s*", $value);
    return $int;
  }

  /**
   * Returns signed 16-bit integer as little-endian ordered binary data.
   *
   * @param integer $value The input value.
   * @return string
   */
  public static function toInt16LE($value)
  {
    if (self::isBigEndian())
      return strrev(self::toInt16($value));
    else
      return self::toInt16($value);
  }

  /**
   * Returns little-endian ordered binary data as signed 16-bit integer.
   *
   * @param string $value The binary data string.
   * @return integer
   */
  public static function fromInt16LE($value)
  {
    if (self::isBigEndian())
      return self::fromInt16(strrev($value));
    else
      return self::fromInt16($value);
  }

  /**
   * Returns signed 16-bit integer as big-endian ordered binary data.
   *
   * @param integer $value The input value.
   * @return string
   */
  public static function toInt16BE($value)
  {
    if (self::isBigEndian())
      return self::toInt16($value);
    else
      return strrev(self::toInt16($value));
  }

  /**
   * Returns big-endian ordered binary data as signed 16-bit integer.
   *
   * @param string $value The binary data string.
   * @return integer
   */
  public static function fromInt16BE($value)
  {
    if (self::isBigEndian())
      return self::fromInt16($value);
    else
      return self::fromInt16(strrev($value));
  }

  /**
   * Returns machine endian ordered binary data as unsigned 16-bit integer.
   *
   * @param string  $value The binary data string.
   * @param integer $order The byte order of the binary data string.
   * @return integer
   */
  private static function fromUInt16($value, $order = self::MACHINE_ENDIAN_ORDER)
  {
    list(, $int) = unpack
      (($order == self::BIG_ENDIAN_ORDER ? "n" :
        ($order == self::LITTLE_ENDIAN_ORDER ? "v" : "S")) . "*", $value);
    return $int;
  }

  /**
   * Returns unsigned 16-bit integer as little-endian ordered binary data.
   *
   * @param integer $value The input value.
   * @return string
   */
  public static function toUInt16LE($value)
  {
    return pack("v*", $value);
  }

  /**
   * Returns little-endian ordered binary data as unsigned 16-bit integer.
   *
   * @param string $value The binary data string.
   * @return integer
   */
  public static function fromUInt16LE($value)
  {
    return self::fromUInt16($value, self::LITTLE_ENDIAN_ORDER);
  }

  /**
   * Returns unsigned 16-bit integer as big-endian ordered binary data.
   *
   * @param integer $value The input value.
   * @return string
   */
  public static function toUInt16BE($value)
  {
    return pack("n*", $value);
  }

  /**
   * Returns big-endian ordered binary data as unsigned 16-bit integer.
   *
   * @param string $value The binary data string.
   * @return integer
   */
  public static function fromUInt16BE($value)
  {
    return self::fromUInt16($value, self::BIG_ENDIAN_ORDER);
  }

  /**
   * Returns an 8-bit integer as binary data.
   *
   * @param integer $value The input value.
   * @return integer
   */
  public static function toInt8($value)
  {
    return pack("c*", $value);
  }

  /**
   * Returns binary data as 8-bit integer.
   *
   * @param string $value The binary data string.
   * @return integer
   */
  public static function fromInt8($value)
  {
    list(, $int) = unpack("c*", $value);
    return $int;
  }

  /**
   * Returns an unsigned 8-bit integer as binary data.
   *
   * @param integer $value The input value.
   * @return integer
   */
  public static function toUInt8($value)
  {
    return pack("C*", $value);
  }

  /**
   * Returns binary data as an unsigned 8-bit integer.
   *
   * @param string $value The binary data string.
   * @return integer
   */
  public static function fromUInt8($value)
  {
    list(, $int) = unpack("C*", $value);
    return $int;
  }

  /**
   * Returns a floating point number as machine endian ordered binary data.
   *
   * @param float $value The input value.
   * @return string
   */
  public static function toFloat($value)
  {
    return pack("f*", $value);
  }

  /**
   * Returns machine endian ordered binary data as a floating point number.
   *
   * @param string $value The binary data string.
   * @return float
   */
  public static function fromFloat($value)
  {
    list(, $float) = unpack("f*", $value);
    return $float;
  }

  /**
   * Returns a floating point number as little-endian ordered binary data.
   *
   * @param float $value The input value.
   * @return string
   */
  public static function toFloatLE($value)
  {
    if (self::isBigEndian())
      return strrev(self::toFloat($value));
    else
      return self::toFloat($value);
  }

  /**
   * Returns little-endian ordered binary data as a floating point number.
   *
   * @param string $value The binary data string.
   * @return float
   */
  public static function fromFloatLE($value)
  {
    if (self::isBigEndian())
      return self::fromFloat(strrev($value));
    else
      return self::fromFloat($value);
  }

  /**
   * Returns a floating point number as big-endian ordered binary data.
   *
   * @param float $value The input value.
   * @return string
   */
  public static function toFloatBE($value)
  {
    if (self::isBigEndian())
      return self::toFloat($value);
    else
      return strrev(self::toFloat($value));
  }

  /**
   * Returns big-endian ordered binary data as a float point number.
   *
   * @param string $value The binary data string.
   * @return float
   */
  public static function fromFloatBE($value)
  {
    if (self::isBigEndian())
      return self::fromFloat($value);
    else
      return self::fromFloat(strrev($value));
  }

  /**
   * Returns string as binary data padded to given length with zeros.
   *
   * @param string $value The input value.
   * @return string
   */
  public static function toString8($value, $length, $padding = "\0")
  {
    return str_pad($value, $length, $padding);
  }

  /**
   * Returns binary data as string. Removes terminating zero.
   *
   * @param string $value The binary data string.
   * @return string
   */
  public static function fromString8($value)
  {
    return rtrim($value, "\0");
  }

  /**
   * Returns machine-ordered multibyte string as UTF-16 defined-order binary
   * data. The byte order is stored using a byte order mask (BOM) in the binary
   * data string.
   *
   * @param string $value The input value.
   * @param integer $order The byte order of the binary data string.
   * @return string
   */
  public static function toString16($value, $order = self::MACHINE_ENDIAN_ORDER)
  {
    $format = $order == self::BIG_ENDIAN_ORDER ? "n" :
      ($order == self::LITTLE_ENDIAN_ORDER ? "v" : "S");
    $string = pack($format, 0xfeff);
    foreach (unpack("S*", $value) as $char)
      $string .= pack($format, $char);
    return $string;
  }

  /**
   * Returns UTF-16 formatted binary data as machine-ordered multibyte string.
   * The byte order is determined from the byte order mark included in the
   * binary data string. The order parameter is updated if a BOM is found.
   *
   * @param string  $value The binary data string.
   * @param integer $order The endian to decode using if no BOM was found.
   * @return string
   */
  public static function fromString16
    ($value, &$order = self::MACHINE_ENDIAN_ORDER)
  {
    if (strlen($value) < 2)
      return "";
    
    if (ord($value[0]) == 0xfe && ord($value[1]) == 0xff) {
      $order = self::BIG_ENDIAN_ORDER;
      return self::fromString16BE(substr($value, 2));
    }
    else if (ord($value[0]) == 0xff && ord($value[1]) == 0xfe) {
      $order = self::LITTLE_ENDIAN_ORDER;
      return self::fromString16LE(substr($value, 2));
    }
    else if ($order == self::BIG_ENDIAN_ORDER ||
      ($order == self::MACHINE_ENDIAN_ORDER && self::isBigEndian()))
      return self::fromString16BE($value);
    else
      return self::fromString16LE($value);
  }

  /**
   * Returns machine-ordered multibyte string as little-endian ordered binary
   * data.
   *
   * @param string $value The input value.
   * @return string
   */
  public static function toString16LE($value)
  {
    $string = "";
    foreach (unpack("S*", $value) as $char)
      $string .= pack("v", $char);
    return $string;
  }

  /**
   * Returns little-endian ordered binary data as machine ordered multibyte
   * string. Removes terminating zero.
   *
   * @param string $value The binary data string.
   * @return string
   */
  public static function fromString16LE($value)
  {
    $string = "";
    foreach (unpack("v*", substr($value, -2) == "\0\0" ?
                    substr($value, 0, -2) : $value) as $char)
      $string .= pack("S", $char);
    return $string;
  }

  /**
   * Returns machine ordered multibyte string as big-endian ordered binary data.
   *
   * @param string $value The input value.
   * @return string
   */
  public static function toString16BE($value)
  {
    $string = "";
    foreach (unpack("S*", $value) as $char)
      $string .= pack("n", $char);
    return $string;
  }

  /**
   * Returns big-endian ordered binary data as machine ordered multibyte string.
   * Removes terminating zero.
   * 
   * @param string $value The binary data string.
   * @return string
   */
  public static function fromString16BE($value)
  {
    $string = "";
    foreach (unpack("n*", substr($value, -2) == "\0\0" ?
                    substr($value, 0, -2) : $value) as $char)
      $string .= pack("S", $char);
    return $string;
  }

  /**
   * Returns hexadecimal string having high nibble first as binary data.
   *
   * @param string $value The input value.
   * @return string
   */
  public static function toHHex($value)
  {
    return pack("H*", $value);
  }

  /**
   * Returns binary data as hexadecimal string having high nibble first.
   *
   * @param string $value The binary data string.
   * @return string
   */
  public static function fromHHex($value)
  {
    list($hex) = unpack("H*0", $value);
    return $hex;
  }

  /**
   * Returns hexadecimal string having low nibble first as binary data.
   *
   * @param string $value The input value.
   * @return string
   */
  public static function toLHex($value)
  {
    return pack("h*", $value);
  }

  /**
   * Returns binary data as hexadecimal string having low nibble first.
   *
   * @param string $value The binary data string.
   * @return string
   */
  public static function fromLHex($value)
  {
    list($hex) = unpack("h*0", $value);
    return $hex;
  }

  /**
   * Returns big-endian ordered hexadecimal GUID string as little-endian ordered
   * binary data string.
   *
   * @param string $value The input value.
   * @return string
   */
  public static function toGUID($value)
  {
    $string = ""; $C = preg_split("/-/", $value);
    return pack
      ("V1v2N2", hexdec($C[0]), hexdec($C[1]), hexdec($C[2]),
       hexdec($C[3] . substr($C[4], 0, 4)), hexdec(substr($C[4], 4)));
  }

  /**
   * Returns the little-endian ordered binary data as big-endian ordered
   * hexadecimal GUID string.
   *
   * @param string $value The binary data string.
   * @return string
   */
  public static function fromGUID($value)
  {
    $C = @unpack("V1V/v2v/N2N", $value);
    list($hex) = @unpack("H*0", pack
      ("NnnNN", $C["V"], $C["v1"], $C["v2"], $C["N1"], $C["N2"]));

    /* Fixes a bug in PHP versions earlier than Jan 25 2006 */
    if (implode("", unpack("H*", pack("H*", "a"))) == "a00")
      $hex = substr($hex, 0, -1);

    return preg_replace
      ("/^(.{8})(.{4})(.{4})(.{4})/", "\\1-\\2-\\3-\\4-", $hex);
  }
}
