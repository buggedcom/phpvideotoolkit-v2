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
 * @version    $Id: Object.php 107 2008-08-03 19:09:16Z svollbehr $
 */

/**
 * The base class for all ID3v2 objects.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @author     Ryan Butterfield <buttza@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 107 $
 */
abstract class ID3_Object
{
  /**
   * The reader object.
   *
   * @var Reader
   */
  protected $_reader;
  
  /**
   * The options array.
   *
   * @var Array
   */
  private $_options;
  
  /**
   * Constructs the class with given parameters and reads object related data
   * from the ID3v2 tag.
   *
   * @param Reader $reader The reader object.
   * @param Array $options The options array.
   */
  public function __construct($reader = null, &$options = array())
  {
    $this->_reader = $reader;
    $this->_options = &$options;
  }
  
  /**
   * Returns the options array.
   *
   * @return Array
   */
  public function getOptions() { return $this->_options; }
  
  /**
   * Returns the given option value, or the default value if the option is not
   * defined.
   *
   * @param string $option The name of the option.
   * @param mixed $defaultValue The default value to be returned.
   */
  public function getOption($option, $defaultValue = false)
  {
    if (isset($this->_options[$option]))
      return $this->_options[$option];
    return $defaultValue;
  }
  
  /**
   * Sets the options array. See {@link ID3v2} class for available options.
   *
   * @param Array $options The options array.
   */
  public function setOptions(&$options) { $this->_options = &$options; }
  
  /**
   * Sets the given option the given value.
   *
   * @param string $option The name of the option.
   * @param mixed $value The value to set for the option.
   */
  public function setOption($option, $value)
  {
    $this->_options[$option] = $value;
  }
  
  /**
   * Magic function so that $obj->value will work.
   *
   * @param string $name The field name.
   * @return mixed
   */
  public function __get($name)
  {
    if (method_exists($this, "get" . ucfirst($name)))
      return call_user_func(array($this, "get" . ucfirst($name)));
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
    if (method_exists($this, "set" . ucfirst($name)))
      call_user_func
        (array($this, "set" . ucfirst($name)), $value);
    else throw new ID3_Exception("Unknown field: " . $name);
  }
  
  /**
   * Encodes the given 32-bit integer to 28-bit synchsafe integer, where the
   * most significant bit of each byte is zero, making seven bits out of eight
   * available.
   * 
   * @param integer $val The integer to encode.
   * @return integer
   */
  protected function encodeSynchsafe32($val)
  {
    return ($val & 0x7f) | ($val & 0x3f80) << 1 | 
      ($val & 0x1fc000) << 2 | ($val & 0xfe00000) << 3;
  }

  /**
   * Decodes the given 28-bit synchsafe integer to regular 32-bit integer.
   * 
   * @param integer $val The integer to decode
   * @return integer
   */
  protected function decodeSynchsafe32($val)
  {
    return ($val & 0x7f) | ($val & 0x7f00) >> 1 | 
      ($val & 0x7f0000) >> 2 | ($val & 0x7f000000) >> 3;
  }
  
  /**
   * Applies the unsynchronisation scheme to the given data string.
   * 
   * Whenever a false synchronisation is found within the data, one zeroed byte
   * is inserted after the first false synchronisation byte. This has the side
   * effect that all 0xff00 combinations have to be altered, so they will not
   * be affected by the decoding process. Therefore all the 0xff00 combinations
   * have to be replaced with the 0xff0000 combination during the
   * unsynchronisation.
   * 
   * @param string $data The input data.
   * @return string
   */
  protected function encodeUnsynchronisation(&$data)
  {
    $result = "";
    for ($i = 0, $j = 0; $i < strlen($data) - 1; $i++)
      if (ord($data[$i]) == 0xff &&
          ((($tmp = ord($data[$i + 1])) & 0xe0) == 0xe0 || $tmp == 0x0)) {
        $result .= substr($data, $j, $i + 1 - $j) . "\0";
        $j = $i + 1;
      }
    return $result . substr($data, $j);
  }
  
  /**
   * Reverses the unsynchronisation scheme from the given data string.
   * 
   * @see encodeUnsyncronisation
   * @param string $data The input data.
   * @return string
   */
  protected function decodeUnsynchronisation(&$data)
  {
    $result = "";
    for ($i = 0, $j = 0; $i < strlen($data) - 1; $i++)
      if (ord($data[$i]) == 0xff && ord($data[$i + 1]) == 0x0) {
        $result .= substr($data, $j, $i + 1 - $j);
        $j = $i + 2;
      }
    return $result . substr($data, $j);
  }
  
  /**
   * Splits UTF-16 formatted binary data up according to null terminators
   * residing in the string, up to a given limit.
   * 
   * @param string $value The input string.
   * @return Array
   */
  protected function explodeString16($value, $limit = null)
  {
    $i = 0;
    $array = array();
    while (count($array) < $limit - 1 || $limit === null) {
      $start = $i;
      do {
        $i = strpos($value, "\x00\x00", $i);
        if ($i === false) {
          $array[] = substr($value, $start);
          return $array;
        }
      } while ($i & 0x1 != 0 && $i++); // make sure its aligned
      $array[] = substr($value, $start, $i - $start);
      $i += 2;
    }
    $array[] = substr($value, $i);
    return $array;
  }
  
  /**
   * Splits UTF-8 or ISO-8859-1 formatted binary data according to null
   * terminators residing in the string, up to a given limit.
   * 
   * @param string $value The input string.
   * @return Array
   */
  protected function explodeString8($value, $limit = null)
  {
    return preg_split("/\\x00/", $value, $limit);
  }
}
