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
 * @subpackage ASF
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: Container.php 108 2008-09-05 17:00:05Z svollbehr $
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * An abstract base container class that contains other ASF objects.
 * 
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 108 $
 */
abstract class ASF_Object_Container extends ASF_Object
{
  /** @var Array */
  private $_objects = array();
  
  /**
   * Reads and constructs the objects found within this object.
   */
  protected function constructObjects($defaultclassnames = array())
  {
    while (true) {
      $offset = $this->_reader->getOffset();
      if ($offset >= $this->getOffset() + $this->getSize())
        break;
      $guid = $this->_reader->readGUID();
      $size = $this->_reader->readInt64LE();
      
      $this->_reader->setOffset($offset);
      if (isset($defaultclassnames[$guid])) {
        if (@fopen($filename = "ASF/Object/" . $defaultclassnames[$guid] .
                   ".php", "r", true) !== false)
          require_once($filename);
        if (class_exists
            ($classname = "ASF_Object_" . $defaultclassnames[$guid]))
          $object = new $classname($this->_reader, $this->_options);
        else
          $object = new ASF_Object($this->_reader, $this->_options);
      } else
        $object = new ASF_Object($this->_reader, $this->_options);
      $object->setParent($this);
      if (!$this->hasObject($object->getIdentifier()))
        $this->_objects[$object->getIdentifier()] = array();
      $this->_objects[$object->getIdentifier()][] = $object;
      $this->_reader->setOffset($offset + $size);
    }
  }
  
  /**
   * Checks whether the object with given GUID is present in the file. Returns
   * <var>true</var> if one or more objects are present, <var>false</var>
   * otherwise.
   * 
   * @return boolean
   */
  public function hasObject($identifier)
  {
    return isset($this->_objects[$identifier]);
  }
  
  /**
   * Returns all the objects the file contains as an associate array. The object
   * identifiers work as keys having an array of ASF objects as associated
   * value.
   * 
   * @return Array
   */
  public function getObjects()
  {
    return $this->_objects;
  }
  
  /**
   * Returns an array of objects matching the given object GUID or an empty
   * array if no object matched the identifier.
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
   */
  public function getObjectsByIdentifier($identifier)
  {
    $matches = array();
    $searchPattern = "/^" .
      str_replace(array("*", "?"), array(".*", "."), $identifier) . "$/i";
    foreach ($this->_objects as $identifier => $objects)
      if (preg_match($searchPattern, $identifier))
        foreach ($objects as $object)
          $matches[] = $object;
    return $matches;
  }
  
  /**
   * Adds a new object into the current object and returns it.
   *
   * @param ASF_Object The object to add
   * @return ASF_Object
   */
  public function addObject($object)
  {
    $object->setParent($this);
    $object->setOptions($this->_options);
    if (!$this->hasObject($object->getIdentifier()))
      $this->_objects[$object->getIdentifier()] = array();
    return $this->_objects[$object->getIdentifier()][] = $object;
  }
  
  /**
   * Override magic function so that $obj->value will work as expected.
   * 
   * The method first attempts to call the appropriate getter method. If no
   * field with given name is found, the method attempts to return the right
   * object instead. In other words, calling $obj->value will attempt to return
   * the first object returned by $this->getObjectsByIdentifier(self::value).
   *
   * @param string $name The field or object name.
   * @return mixed
   */
  public function __get($name)
  {
    if (method_exists($this, "get" . ucfirst($name)))
      return call_user_func(array($this, "get" . ucfirst($name)));
    if (defined($constname = get_class($this) . "::" . strtoupper
                (preg_replace("/[A-Z]/", "_$0", $name)))) {
      $objects = $this->getObjectsByIdentifier(constant($constname));
      if (isset($objects[0]))
        return $objects[0];
    }
    throw new ASF_Exception("Unknown field/object: " . $name);
  }
  
  /**
   * Magic function so that isset($obj->value) will work. This method checks
   * whether the object by given identifier is contained by this container.
   *
   * @param string $name The object name.
   * @return boolean
   */
  public function __isset($name)
  {
    if (defined($constname = get_class($this) . "::" . strtoupper
                (preg_replace("/[A-Z]/", "_$0", $name)))) {
      $objects = $this->getObjectsByIdentifier(constant($constname));
      return isset($objects[0]);
    }
    else
      return isset($this->_objects[$name]);
  }
}
