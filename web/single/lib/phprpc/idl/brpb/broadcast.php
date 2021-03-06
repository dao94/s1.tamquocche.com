<?php
// DO NOT EDIT! Generated by Protobuf-PHP protoc plugin 0.9.4
// Source: brpb/broadcast.proto
//   Date: 2014-12-19 08:23:04

namespace brpb {

  class BdcInfo extends \DrSlump\Protobuf\Message {

    /**  @var string[]  */
    public $msgId = array();
    
    /**  @var int */
    public $state = null;
    

    /** @var \Closure[] */
    protected static $__extensions = array();

    public static function descriptor()
    {
      $descriptor = new \DrSlump\Protobuf\Descriptor(__CLASS__, 'brpb.BdcInfo');

      // REPEATED STRING msgId = 1
      $f = new \DrSlump\Protobuf\Field();
      $f->number    = 1;
      $f->name      = "msgId";
      $f->type      = \DrSlump\Protobuf::TYPE_STRING;
      $f->rule      = \DrSlump\Protobuf::RULE_REPEATED;
      $descriptor->addField($f);

      // REQUIRED INT32 state = 2
      $f = new \DrSlump\Protobuf\Field();
      $f->number    = 2;
      $f->name      = "state";
      $f->type      = \DrSlump\Protobuf::TYPE_INT32;
      $f->rule      = \DrSlump\Protobuf::RULE_REQUIRED;
      $descriptor->addField($f);

      foreach (self::$__extensions as $cb) {
        $descriptor->addField($cb(), true);
      }

      return $descriptor;
    }

    /**
     * Check if <msgId> has a value
     *
     * @return boolean
     */
    public function hasMsgId(){
      return $this->_has(1);
    }
    
    /**
     * Clear <msgId> value
     *
     * @return \brpb\BdcInfo
     */
    public function clearMsgId(){
      return $this->_clear(1);
    }
    
    /**
     * Get <msgId> value
     *
     * @param int $idx
     * @return string
     */
    public function getMsgId($idx = NULL){
      return $this->_get(1, $idx);
    }
    
    /**
     * Set <msgId> value
     *
     * @param string $value
     * @return \brpb\BdcInfo
     */
    public function setMsgId( $value, $idx = NULL){
      return $this->_set(1, $value, $idx);
    }
    
    /**
     * Get all elements of <msgId>
     *
     * @return string[]
     */
    public function getMsgIdList(){
     return $this->_get(1);
    }
    
    /**
     * Add a new element to <msgId>
     *
     * @param string $value
     * @return \brpb\BdcInfo
     */
    public function addMsgId( $value){
     return $this->_add(1, $value);
    }
    
    /**
     * Check if <state> has a value
     *
     * @return boolean
     */
    public function hasState(){
      return $this->_has(2);
    }
    
    /**
     * Clear <state> value
     *
     * @return \brpb\BdcInfo
     */
    public function clearState(){
      return $this->_clear(2);
    }
    
    /**
     * Get <state> value
     *
     * @return int
     */
    public function getState(){
      return $this->_get(2);
    }
    
    /**
     * Set <state> value
     *
     * @param int $value
     * @return \brpb\BdcInfo
     */
    public function setState( $value){
      return $this->_set(2, $value);
    }
  }
}

