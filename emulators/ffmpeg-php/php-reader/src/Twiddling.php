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
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: Twiddling.php 110 2008-09-05 17:10:51Z svollbehr $
 */

/**
 * A utility class to perform bit twiddling on integers.
 *
 * @package    php-reader
 * @author     Ryan Butterfield <buttza@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 110 $
 * @static
 */
final class Twiddling
{
  /**
   * Default private constructor for a static class.
   */
  private function __construct() {}

  /**
   * Sets a bit at a given position in an integer.
   *
   * @param integer $integer  The value to manipulate.
   * @param integer $position The position of the bit to set.
   * @param boolean $on       Whether to enable or clear the bit.
   * @return integer
   */
  public static function setBit($integer, $position, $on)
  {
    return $on ? self::enableBit($integer, $position) :
      self::clearBit($integer, $position);
  }

  /**
   * Enables a bit at a given position in an integer.
   *
   * @param integer $integer  The value to manipulate.
   * @param integer $position The position of the bit to enable.
   * @return integer
   */
  public static function enableBit($integer, $position)
  {
    return $integer | (1 << $position);
  }

  /**
   * Clears a bit at a given position in an integer.
   *
   * @param integer $integer  The value to manipulate.
   * @param integer $position The position of the bit to clear.
   * @return integer
   */
  public static function clearBit($integer, $position)
  {
    return $integer & ~(1 << $position);
  }

  /**
   * Toggles a bit at a given position in an integer.
   *
   * @param integer $integer  The value to manipulate.
   * @param integer $position The position of the bit to toggle.
   * @return integer
   */
  public static function toggleBit($integer, $position)
  {
    return $integer ^ (1 << $position);
  }

  /**
   * Tests a bit at a given position in an integer.
   *
   * @param integer $integer  The value to test.
   * @param integer $position The position of the bit to test.
   * @return boolean
   */
  public static function testBit($integer, $position)
  {
    return ($integer & (1 << $position)) != 0;
  }

  /**
   * Sets a given set of bits in an integer.
   *
   * @param integer $integer The value to manipulate.
   * @param integer $bits    The bits to set.
   * @param boolean $on      Whether to enable or clear the bits.
   * @return integer
   */
  public static function setBits($integer, $bits, $on)
  {
    return $on ? self::enableBits($integer, $bits) :
      self::clearBits($integer, $bits);
  }

  /**
   * Enables a given set of bits in an integer.
   *
   * @param integer $integer The value to manipulate.
   * @param integer $bits    The bits to enable.
   * @return integer
   */
  public static function enableBits($integer, $bits)
  {
    return $integer | $bits;
  }

  /**
   * Clears a given set of bits in an integer.
   *
   * @param integer $integer The value to manipulate.
   * @param integer $bits    The bits to clear.
   * @return integer
   */
  public static function clearBits($integer, $bits)
  {
    return $integer & ~$bits;
  }

  /**
   * Toggles a given set of bits in an integer.
   *
   * @param integer $integer The value to manipulate.
   * @param integer $bits    The bits to toggle.
   * @return integer
   */
  public static function toggleBits($integer, $bits)
  {
    return $integer ^ $bits;
  }

  /**
   * Tests a given set of bits in an integer
   * returning whether all bits are set.
   *
   * @param integer $integer The value to test.
   * @param integer $bits    The bits to test.
   * @return boolean
   */
  public static function testAllBits($integer, $bits)
  {
    return ($integer & $bits) == $bits;
  }

  /**
   * Tests a given set of bits in an integer
   * returning whether any bits are set.
   *
   * @param integer $integer The value to test.
   * @param integer $bits    The bits to test.
   * @return boolean
   */
  public static function testAnyBits($integer, $bits)
  {
    return ($integer & $bits) != 0;
  }

  /**
   * Stores a value in a given range in an integer.
   *
   * @param integer $integer The value to store into.
   * @param integer $start   The position to store from. Must be <= $end.
   * @param integer $end     The position to store to. Must be >= $start.
   * @param integer $value   The value to store.
   * @return integer
   */
  public static function setValue($integer, $start, $end, $value)
  {
    return self::clearBits($integer, self::getMask($start, $end) << $start) |
      ($value << $start);
  }

  /**
   * Retrieves a value from a given range in an integer, inclusive.
   *
   * @param integer $integer The value to read from.
   * @param integer $start   The position to read from. Must be <= $end.
   * @param integer $end     The position to read to. Must be >= $start.
   * @return integer
   */
  public static function getValue($integer, $start, $end)
  {
    return ($integer & self::getMask($start, $end)) >> $start;
  }

  /**
   * Returns an integer with all bits set from start to end.
   *
   * @param integer $start The position to start setting bits from. Must
   *                       be <= $end.
   * @param integer $end   The position to stop setting bits. Must be >= $start.
   * @return integer
   */
  public static function getMask($start, $end)
  {
    $mask = 0;
    for (; $start <= $end; $start++)
      $mask |= 1 << $start;
    return $mask;
  }
}
