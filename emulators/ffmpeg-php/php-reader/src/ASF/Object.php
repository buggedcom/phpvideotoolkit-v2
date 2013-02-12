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
 * @package    php-reader
 * @subpackage ASF
 * @copyright  Copyright (c) 2006-2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: Object.php 102 2008-06-23 20:41:20Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Exception.php");
/**#@-*/

/**
 * The base unit of organization for ASF files is called the ASF object. It
 * consists of a 128-bit GUID for the object, a 64-bit integer object size, and
 * the variable-length object data.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2006-2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 102 $
 */
class ASF_Object
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
  protected $_options;

  /** @var integer */
  private $_offset = -1;
  
  /** @var string */
  private $_id;
  
  /** @var integer */
  private $_size = -1;
  
  /** @var ASF_Object */
  private $_parent = null;
  
  /**
   * Constructs the class with given parameters and options.
   *
   * @param Reader $reader  The reader object.
   * @param Array  $options The options array.
   */
  public function __construct($reader, &$options = array())
  {
    $this->_reader = $reader;
    $this->_options = $options;
    $this->_offset = $this->_reader->getOffset();
    $this->_id = $this->_reader->readGUID();
    $this->_size = $this->_reader->readInt64LE();
  }
  
  /**
   * Returns the file offset to box start, or -1 if the box was created on heap.
   * 
   * @return integer
   */
  public function getOffset() { return $this->_offset; }
  
  /**
   * Sets the file offset where the box starts.
   * 
   * @param integer $offset The file offset to box start.
   */
  public function setOffset($offset) { $this->_offset = $offset; }
  
  /**
   * Returns the GUID of the ASF object.
   * 
   * @return string
   */
  public function getIdentifier() { return $this->_id; }
  
  /**
   * Set the GUID of the ASF object.
   * 
   * @param string $id The GUID
   */
  public function setIdentifier($id) { $this->_id = $id; }
  
  /**
   * Returns the object size in bytes, including the header.
   * 
   * @return integer
   */
  public function getSize() { return $this->_size; }
  
  /**
   * Sets the box size. The size must include the header.
   * 
   * @param integer $size The box size.
   */
  public function setSize($size)
  {
    if ($this->_parent !== null)
      $this->_parent->setSize
        (($this->_parent->getSize() > 0 ? $this->_parent->getSize() : 0) +
         $size - ($this->_size > 0 ? $this->_size : 0));
    $this->_size = $size;
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
   * Sets the options array. See {@link ISO14496} class for available options.
   *
   * @param Array $options The options array.
   */
  public function setOptions(&$options) { $this->_options = $options; }
  
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
   * Returns the parent object containing this box.
   * 
   * @return ASF_Object
   */
  public function getParent() { return $this->_parent; }
  
  /**
   * Sets the parent containing object.
   * 
   * @param ASF_Object $parent The parent object.
   */
  public function setParent(&$parent) { $this->_parent = $parent; }
  
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
    throw new ASF_Exception("Unknown field: " . $name);
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
      call_user_func(array($this, "set" . ucfirst($name)), $value);
    else throw new ASF_Exception("Unknown field: " . $name);
  }
}
