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
 * @subpackage ISO 14496
 * @copyright  Copyright (c) 2008 PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: Box.php 102 2008-06-23 20:41:20Z svollbehr $
 */

/**#@+ @ignore */
require_once("ISO14496/Exception.php");
/**#@-*/

/**
 * A base class for all ISO 14496-12 boxes.
 * 
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 102 $
 */
class ISO14496_Box
{
  /**
   * The reader object.
   *
   * @var Reader
   */
  protected $_reader;
  
  /** @var Array */
  private $_options;
  
  /** @var integer */
  private $_offset = -1;
  
  /** @var integer */
  private $_size = -1;
  
  /** @var string */
  private $_type;
  
  
  /** @var ISO14496_Box */
  private $_parent = null;
  
  
  /** @var boolean */
  private $_container = false;
  
  /** @var Array */
  private $_boxes = array();

  /** @var Array */
  private static $_path = array();
  
  /**
   * Constructs the class with given parameters and options.
   *
   * @param Reader $reader  The reader object.
   * @param Array  $options The options array.
   */
  public function __construct($reader, &$options = array())
  {
    if (($this->_reader = $reader) === null) {
      $this->_type = strtolower(substr(get_class($this), -4));
    } else {
      $this->_offset = $this->_reader->getOffset();
      $this->_size = $this->_reader->readUInt32BE();
      $this->_type = $this->_reader->read(4);
    
      if ($this->_size == 1)
        $this->_size = $this->_reader->readInt64BE();
      if ($this->_size == 0)
        $this->_size = $this->_reader->getSize() - $this->_offset;

      if ($this->_type == "uuid")
        $this->_type = $this->_reader->readGUID();
    }
    $this->_options = $options;
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
   * Returns the box size in bytes, including the size and type header,
   * fields, and all contained boxes, or -1 if the box was created on heap.
   * 
   * @return integer
   */
  public function getSize() { return $this->_size; }
  
  /**
   * Sets the box size. The size must include the size and type header,
   * fields, and all contained boxes.
   *
   * The method will propagate size change to box parents.
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
   * Returns the box type.
   * 
   * @return string
   */
  public function getType() { return $this->_type; }
  
  /**
   * Sets the box type.
   * 
   * @param string $type The box type.
   */
  public function setType($type) { $this->_type = $type; }
  
  /**
   * Returns the parent box containing this box.
   * 
   * @return ISO14496_Box
   */
  public function getParent() { return $this->_parent; }
  
  /**
   * Sets the parent containing box.
   * 
   * @param ISO14496_Box $parent The parent box.
   */
  public function setParent(&$parent) { $this->_parent = $parent; }
  
  /**
   * Returns a boolean value corresponding to whether the box is a container.
   * 
   * @return boolean
   */
  public function isContainer() { return $this->_container; }
  
  /**
   * Returns a boolean value corresponding to whether the box is a container.
   * 
   * @return boolean
   */
  public function getContainer() { return $this->_container; }
  
  /**
   * Sets whether the box is a container.
   * 
   * @param boolean $container Whether the box is a container.
   */
  protected function setContainer($container)
  {
    $this->_container = $container;
  }

  /**
   * Reads and constructs the boxes found within this box.
   *
   * @todo Does not parse iTunes internal ---- boxes.
   */
  protected function constructBoxes($defaultclassname = "ISO14496_Box")
  {
    $base = $this->getOption("base", "");
    if ($this->getType() != "file")
      self::$_path[] = $this->getType();
    $path = implode(self::$_path, ".");
    
    while (true) {
      $offset = $this->_reader->getOffset();
      if ($offset >= $this->_offset + $this->_size)
        break;
      $size = $this->_reader->readUInt32BE();
      $type = rtrim($this->_reader->read(4), " ");
      if ($size == 1)
        $size = $this->_reader->readInt64BE();
      if ($size == 0)
        $size = $this->_reader->getSize() - $offset;
      
      if (preg_match("/^\xa9?[a-z0-9]{3,4}$/i", $type) &&
          substr($base, 0, min(strlen($base), strlen
                               ($tmp = $path . ($path ? "." : "") . $type))) ==
          substr($tmp,  0, min(strlen($base), strlen($tmp))))
      {
        $this->_reader->setOffset($offset);
        if (@fopen($filename = "ISO14496/Box/" . strtoupper($type) . ".php",
                   "r", true) !== false)
          require_once($filename);
        if (class_exists($classname = "ISO14496_Box_" . strtoupper($type)))
          $box = new $classname($this->_reader, $this->_options);
        else
          $box = new $defaultclassname($this->_reader, $this->_options);
        $box->setParent($this);
        if (!isset($this->_boxes[$box->getType()]))
          $this->_boxes[$box->getType()] = array();
        $this->_boxes[$box->getType()][] = $box;
      }
      $this->_reader->setOffset($offset + $size);
    }
    
    array_pop(self::$_path);
  }
  
  /**
   * Checks whether the box given as an argument is present in the file. Returns
   * <var>true</var> if one or more boxes are present, <var>false</var>
   * otherwise.
   * 
   * @return boolean
   * @throws ISO14496_Exception if called on a non-container box
   */
  public function hasBox($identifier)
  {
    if (!$this->isContainer())
      throw new ISO14496_Exception("Box not a container");
    return isset($this->_boxes[$identifier]);
  }
  
  /**
   * Returns all the boxes the file contains as an associate array. The box
   * identifiers work as keys having an array of boxes as associated value.
   * 
   * @return Array
   * @throws ISO14496_Exception if called on a non-container box
   */
  public function getBoxes()
  {
    if (!$this->isContainer())
      throw new ISO14496_Exception("Box not a container");
    return $this->_boxes;
  }
  
  /**
   * Returns an array of boxes matching the given identifier or an empty array
   * if no boxes matched the identifier.
   *
   * The identifier may contain wildcard characters "*" and "?". The asterisk
   * matches against zero or more characters, and the question mark matches any
   * single character.
   *
   * Please note that one may also use the shorthand $obj->identifier to access
   * the first box with the identifier given. Wildcards cannot be used with
   * the shorthand and they will not work with user defined uuid types.
   * 
   * @return Array
   * @throws ISO14496_Exception if called on a non-container box
   */
  public function getBoxesByIdentifier($identifier)
  {
    if (!$this->isContainer())
      throw new ISO14496_Exception("Box not a container");
    $matches = array();
    $searchPattern = "/^" .
      str_replace(array("*", "?"), array(".*", "."), $identifier) . "$/i";
    foreach ($this->_boxes as $identifier => $boxes)
      if (preg_match($searchPattern, $identifier))
        foreach ($boxes as $box)
          $matches[] = $box;
    return $matches;
  }
  
  /**
   * Adds a new box into the current box and returns it.
   *
   * @param ISO14496_Box The box to add
   * @return ISO14496_Box
   */
  public function addBox($box)
  {
    $box->setParent($this);
    $box->setOptions($this->_options);
    if (!$this->hasBox($box->getType()))
      $this->_boxes[$box->getType()] = array();
    return $this->_boxes[$box->getType()][] = $box;
  }
  
  /**
   * Magic function so that $obj->value will work. If called on a container box,
   * the method will first attempt to return the first contained box that
   * matches the identifier, and if not found, invoke a getter method.
   *
   * If there are no boxes or getter methods with given name, the method
   * attempts to create a frame with given identifier.
   *
   * If none of these work, an exception is thrown.
   *
   * @param string $name The box or field name.
   * @return mixed
   */
  public function __get($name)
  {
    if ($this->isContainer() && isset($this->_boxes[$name]))
      return $this->_boxes[$name][0];
    if (method_exists($this, "get" . ucfirst($name)))
      return call_user_func(array($this, "get" . ucfirst($name)));
    if (@fopen($filename = "ISO14496/Box/" .
               strtoupper($name) . ".php", "r", true) !== false)
      require_once($filename);
    if (class_exists($classname = "ISO14496_Box_" . strtoupper($name)))
      return $this->addBox(new $classname());
    throw new ISO14496_Exception("Unknown box/field: " . $name);
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
    else throw new ISO14496_Exception("Unknown field: " . $name);
  }
  
  /**
   * Magic function so that isset($obj->value) will work. This method checks
   * whether the box is a container and contains a box that matches the
   * identifier.
   *
   * @param string $name The box name.
   * @return boolean
   */
  public function __isset($name)
  {
    return ($this->isContainer() && isset($this->_boxes[$name]));
  }
  
  /**
   * Magic function so that unset($obj->value) will work. This method removes
   * all the boxes from this container that match the identifier.
   *
   * @param string $name The box name.
   */
  public function __unset($name)
  {
    if ($this->isContainer())
      unset($this->_boxes[$name]);
  }
  
  /**
   * Returns the box raw data.
   *
   * @return string
   */
  public function __toString($data = "")
  {
    if ($this->isContainer())
      foreach ($this->getBoxes() as $name => $boxes)
        foreach ($boxes as $box)
          $data .= $box;
    $size = strlen($data) + 8;
    if ($size > 0xffffffff)
      $size += 8;
    if (strlen($this->_type) > 4)
      $size += 16;
    return ($size > 0xffffffff ?
             Transform::toUInt32BE(1) : Transform::toUInt32BE($size)) .
      (strlen($this->_type) > 4 ? "uuid" : $this->_type) .
      ($size > 0xffffffff ? Transform::toInt64BE($size) : "") . 
      (strlen($this->_type) > 4 ? Transform::toGUID($this->_type) : "") . $data;
  }
}
