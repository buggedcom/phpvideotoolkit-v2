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
 * @version    $Id: RVRB.php 105 2008-07-30 14:56:47Z svollbehr $
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
/**#@-*/

/**
 * The <i>Reverb</i> is yet another subjective frame, with which you can adjust
 * echoes of different kinds. Reverb left/right is the delay between every
 * bounce in milliseconds. Reverb bounces left/right is the number of bounces
 * that should be made. $FF equals an infinite number of bounces. Feedback is
 * the amount of volume that should be returned to the next echo bounce. $00 is
 * 0%, $FF is 100%. If this value were $7F, there would be 50% volume reduction
 * on the first bounce, 50% of that on the second and so on. Left to left means
 * the sound from the left bounce to be played in the left speaker, while left
 * to right means sound from the left bounce to be played in the right speaker.
 *
 * Premix left to right is the amount of left sound to be mixed in the right
 * before any reverb is applied, where $00 id 0% and $FF is 100%. Premix right
 * to left does the same thing, but right to left. Setting both premix to $FF
 * would result in a mono output (if the reverb is applied symmetric). There may
 * only be one RVRB frame in each tag.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @author     Ryan Butterfield <buttza@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 105 $
 */
final class ID3_Frame_RVRB extends ID3_Frame
{
  /** @var integer */
  private $_reverbLeft;
  
  /** @var integer */
  private $_reverbRight;
  
  /** @var integer */
  private $_reverbBouncesLeft;
  
  /** @var integer */
  private $_reverbBouncesRight;
  
  /** @var integer */
  private $_reverbFeedbackLtoL;
  
  /** @var integer */
  private $_reverbFeedbackLtoR;

  /** @var integer */
  private $_reverbFeedbackRtoR;

  /** @var integer */
  private $_reverbFeedbackRtoL;
  
  /** @var integer */
  private $_premixLtoR;
  
  /** @var integer */
  private $_premixRtoL;
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   * @param Array $options The options array.
   */
  public function __construct($reader = null, &$options = array())
  {
    parent::__construct($reader, $options);
    
    if ($reader === null)
      return;

    $this->_reverbLeft  = Transform::fromUInt16BE(substr($this->_data, 0, 2));
    $this->_reverbRight = Transform::fromUInt16BE(substr($this->_data, 2, 2));
    $this->_reverbBouncesLeft  = Transform::fromUInt8($this->_data[4]);
    $this->_reverbBouncesRight = Transform::fromUInt8($this->_data[5]);
    $this->_reverbFeedbackLtoL = Transform::fromUInt8($this->_data[6]);
    $this->_reverbFeedbackLtoR = Transform::fromUInt8($this->_data[7]);
    $this->_reverbFeedbackRtoR = Transform::fromUInt8($this->_data[8]);
    $this->_reverbFeedbackRtoL = Transform::fromUInt8($this->_data[9]);
    $this->_premixLtoR  = Transform::fromUInt8($this->_data[10]);
    $this->_premixRtoL  = Transform::fromUInt8($this->_data[11]);
  }
  
  /**
   * Returns the left reverb.
   * 
   * @return integer
   */
  public function getReverbLeft() { return $this->_reverbLeft; }
  
  /**
   * Sets the left reverb.
   * 
   * @param integer $reverbLeft The left reverb.
   */
  public function setReverbLeft($reverbLeft)
  {
    return $this->_reverbLeft = $reverbLeft;
  }
  
  /**
   * Returns the right reverb.
   * 
   * @return integer
   */
  public function getReverbRight() { return $this->_reverbRight; }
  
  /**
   * Sets the right reverb.
   * 
   * @param integer $reverbRight The right reverb.
   */
  public function setReverbRight($reverbRight)
  {
    return $this->_reverbRight = $reverbRight;
  }
  
  /**
   * Returns the left reverb bounces.
   * 
   * @return integer
   */
  public function getReverbBouncesLeft() { return $this->_reverbBouncesLeft; }
  
  /**
   * Sets the left reverb bounces.
   * 
   * @param integer $reverbBouncesLeft The left reverb bounces.
   */
  public function setReverbBouncesLeft($reverbBouncesLeft)
  {
    $this->_reverbBouncesLeft = $reverbBouncesLeft;
  }
  
  /**
   * Returns the right reverb bounces.
   * 
   * @return integer
   */
  public function getReverbBouncesRight() { return $this->_reverbBouncesRight; }
  
  /**
   * Sets the right reverb bounces.
   * 
   * @param integer $reverbBouncesRight The right reverb bounces.
   */
  public function setReverbBouncesRight($reverbBouncesRight)
  {
    $this->_reverbBouncesRight = $reverbBouncesRight;
  }
  
  /**
   * Returns the left-to-left reverb feedback.
   * 
   * @return integer
   */
  public function getReverbFeedbackLtoL() { return $this->_reverbFeedbackLtoL; }
  
  /**
   * Sets the left-to-left reverb feedback.
   * 
   * @param integer $reverbFeedbackLtoL The left-to-left reverb feedback.
   */
  public function setReverbFeedbackLtoL($reverbFeedbackLtoL)
  {
    $this->_reverbFeedbackLtoL = $reverbFeedbackLtoL;
  }
  
  /**
   * Returns the left-to-right reverb feedback.
   * 
   * @return integer
   */
  public function getReverbFeedbackLtoR() { return $this->_reverbFeedbackLtoR; }
  
  /**
   * Sets the left-to-right reverb feedback.
   * 
   * @param integer $reverbFeedbackLtoR The left-to-right reverb feedback.
   */
  public function setReverbFeedbackLtoR($reverbFeedbackLtoR)
  {
    $this->_reverbFeedbackLtoR = $reverbFeedbackLtoR;
  }
  
  /**
   * Returns the right-to-right reverb feedback.
   * 
   * @return integer
   */
  public function getReverbFeedbackRtoR() { return $this->_reverbFeedbackRtoR; }
  
  /**
   * Sets the right-to-right reverb feedback.
   * 
   * @param integer $reverbFeedbackRtoR The right-to-right reverb feedback.
   */
  public function setReverbFeedbackRtoR($reverbFeedbackRtoR)
  {
    $this->_reverbFeedbackRtoR = $reverbFeedbackRtoR;
  }
  
  /**
   * Returns the right-to-left reverb feedback.
   * 
   * @return integer
   */
  public function getReverbFeedbackRtoL() { return $this->_reverbFeedbackRtoL; }
  
  /**
   * Sets the right-to-left reverb feedback.
   * 
   * @param integer $reverbFeedbackRtoL The right-to-left reverb feedback.
   */
  public function setReverbFeedbackRtoL($reverbFeedbackRtoL)
  {
    $this->_reverbFeedbackRtoL = $reverbFeedbackRtoL;
  }
  
  /**
   * Returns the left-to-right premix.
   * 
   * @return integer
   */
  public function getPremixLtoR() { return $this->_premixLtoR; }
  
  /**
   * Sets the left-to-right premix.
   * 
   * @param integer $premixLtoR The left-to-right premix.
   */
  public function setPremixLtoR($premixLtoR)
  {
    $this->_premixLtoR = $premixLtoR;
  }
  
  /**
   * Returns the right-to-left premix.
   * 
   * @return integer
   */
  public function getPremixRtoL() { return $this->_premixRtoL; }
  
  /**
   * Sets the right-to-left premix.
   * 
   * @param integer $premixRtoL The right-to-left premix.
   */
  public function setPremixRtoL($premixRtoL)
  {
    $this->_premixRtoL = $premixRtoL;
  }
  
  /**
   * Returns the frame raw data.
   *
   * @return string
   */
  public function __toString()
  {
    $this->setData
      (Transform::toUInt16BE($this->_reverbLeft) .
       Transform::toUInt16BE($this->_reverbRight) .
       Transform::toUInt8($this->_reverbBouncesLeft) .
       Transform::toUInt8($this->_reverbBouncesRight) .
       Transform::toUInt8($this->_reverbFeedbackLtoL) .
       Transform::toUInt8($this->_reverbFeedbackLtoR) .
       Transform::toUInt8($this->_reverbFeedbackRtoR) .
       Transform::toUInt8($this->_reverbFeedbackRtoL) .
       Transform::toUInt8($this->_premixLtoR) .
       Transform::toUInt8($this->_premixRtoL));
    return parent::__toString();
  }
}
