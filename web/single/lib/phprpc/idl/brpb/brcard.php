<?php
// DO NOT EDIT! Generated by Protobuf-PHP protoc plugin 0.9.4
// Source: brpb/brcard.proto
//   Date: 2014-12-19 08:23:04

namespace brpb {

  class CardStatus extends \DrSlump\Protobuf\Message {

    /**  @var int[]  */
    public $type = array();
    
    /**  @var int */
    public $status = null;
    

    /** @var \Closure[] */
    protected static $__extensions = array();

    public static function descriptor()
    {
      $descriptor = new \DrSlump\Protobuf\Descriptor(__CLASS__, 'brpb.CardStatus');

      // REPEATED INT32 type = 1
      $f = new \DrSlump\Protobuf\Field();
      $f->number    = 1;
      $f->name      = "type";
      $f->type      = \DrSlump\Protobuf::TYPE_INT32;
      $f->rule      = \DrSlump\Protobuf::RULE_REPEATED;
      $descriptor->addField($f);

      // REQUIRED INT32 status = 2
      $f = new \DrSlump\Protobuf\Field();
      $f->number    = 2;
      $f->name      = "status";
      $f->type      = \DrSlump\Protobuf::TYPE_INT32;
      $f->rule      = \DrSlump\Protobuf::RULE_REQUIRED;
      $descriptor->addField($f);

      foreach (self::$__extensions as $cb) {
        $descriptor->addField($cb(), true);
      }

      return $descriptor;
    }

    /**
     * Check if <type> has a value
     *
     * @return boolean
     */
    public function hasType(){
      return $this->_has(1);
    }
    
    /**
     * Clear <type> value
     *
     * @return \brpb\CardStatus
     */
    public function clearType(){
      return $this->_clear(1);
    }
    
    /**
     * Get <type> value
     *
     * @param int $idx
     * @return int
     */
    public function getType($idx = NULL){
      return $this->_get(1, $idx);
    }
    
    /**
     * Set <type> value
     *
     * @param int $value
     * @return \brpb\CardStatus
     */
    public function setType( $value, $idx = NULL){
      return $this->_set(1, $value, $idx);
    }
    
    /**
     * Get all elements of <type>
     *
     * @return int[]
     */
    public function getTypeList(){
     return $this->_get(1);
    }
    
    /**
     * Add a new element to <type>
     *
     * @param int $value
     * @return \brpb\CardStatus
     */
    public function addType( $value){
     return $this->_add(1, $value);
    }
    
    /**
     * Check if <status> has a value
     *
     * @return boolean
     */
    public function hasStatus(){
      return $this->_has(2);
    }
    
    /**
     * Clear <status> value
     *
     * @return \brpb\CardStatus
     */
    public function clearStatus(){
      return $this->_clear(2);
    }
    
    /**
     * Get <status> value
     *
     * @return int
     */
    public function getStatus(){
      return $this->_get(2);
    }
    
    /**
     * Set <status> value
     *
     * @param int $value
     * @return \brpb\CardStatus
     */
    public function setStatus( $value){
      return $this->_set(2, $value);
    }
  }
}

namespace brpb {

  class NotifyCardGift extends \DrSlump\Protobuf\Message {

    /**  @var float */
    public $charId = null;
    
    /**  @var int */
    public $cardId = null;
    
    /**  @var int */
    public $flag = null;
    
    /**  @var string */
    public $code = null;
    

    /** @var \Closure[] */
    protected static $__extensions = array();

    public static function descriptor()
    {
      $descriptor = new \DrSlump\Protobuf\Descriptor(__CLASS__, 'brpb.NotifyCardGift');

      // REQUIRED DOUBLE charId = 1
      $f = new \DrSlump\Protobuf\Field();
      $f->number    = 1;
      $f->name      = "charId";
      $f->type      = \DrSlump\Protobuf::TYPE_DOUBLE;
      $f->rule      = \DrSlump\Protobuf::RULE_REQUIRED;
      $descriptor->addField($f);

      // REQUIRED INT32 cardId = 2
      $f = new \DrSlump\Protobuf\Field();
      $f->number    = 2;
      $f->name      = "cardId";
      $f->type      = \DrSlump\Protobuf::TYPE_INT32;
      $f->rule      = \DrSlump\Protobuf::RULE_REQUIRED;
      $descriptor->addField($f);

      // REQUIRED INT32 flag = 3
      $f = new \DrSlump\Protobuf\Field();
      $f->number    = 3;
      $f->name      = "flag";
      $f->type      = \DrSlump\Protobuf::TYPE_INT32;
      $f->rule      = \DrSlump\Protobuf::RULE_REQUIRED;
      $descriptor->addField($f);

      // REQUIRED STRING code = 4
      $f = new \DrSlump\Protobuf\Field();
      $f->number    = 4;
      $f->name      = "code";
      $f->type      = \DrSlump\Protobuf::TYPE_STRING;
      $f->rule      = \DrSlump\Protobuf::RULE_REQUIRED;
      $descriptor->addField($f);

      foreach (self::$__extensions as $cb) {
        $descriptor->addField($cb(), true);
      }

      return $descriptor;
    }

    /**
     * Check if <charId> has a value
     *
     * @return boolean
     */
    public function hasCharId(){
      return $this->_has(1);
    }
    
    /**
     * Clear <charId> value
     *
     * @return \brpb\NotifyCardGift
     */
    public function clearCharId(){
      return $this->_clear(1);
    }
    
    /**
     * Get <charId> value
     *
     * @return float
     */
    public function getCharId(){
      return $this->_get(1);
    }
    
    /**
     * Set <charId> value
     *
     * @param float $value
     * @return \brpb\NotifyCardGift
     */
    public function setCharId( $value){
      return $this->_set(1, $value);
    }
    
    /**
     * Check if <cardId> has a value
     *
     * @return boolean
     */
    public function hasCardId(){
      return $this->_has(2);
    }
    
    /**
     * Clear <cardId> value
     *
     * @return \brpb\NotifyCardGift
     */
    public function clearCardId(){
      return $this->_clear(2);
    }
    
    /**
     * Get <cardId> value
     *
     * @return int
     */
    public function getCardId(){
      return $this->_get(2);
    }
    
    /**
     * Set <cardId> value
     *
     * @param int $value
     * @return \brpb\NotifyCardGift
     */
    public function setCardId( $value){
      return $this->_set(2, $value);
    }
    
    /**
     * Check if <flag> has a value
     *
     * @return boolean
     */
    public function hasFlag(){
      return $this->_has(3);
    }
    
    /**
     * Clear <flag> value
     *
     * @return \brpb\NotifyCardGift
     */
    public function clearFlag(){
      return $this->_clear(3);
    }
    
    /**
     * Get <flag> value
     *
     * @return int
     */
    public function getFlag(){
      return $this->_get(3);
    }
    
    /**
     * Set <flag> value
     *
     * @param int $value
     * @return \brpb\NotifyCardGift
     */
    public function setFlag( $value){
      return $this->_set(3, $value);
    }
    
    /**
     * Check if <code> has a value
     *
     * @return boolean
     */
    public function hasCode(){
      return $this->_has(4);
    }
    
    /**
     * Clear <code> value
     *
     * @return \brpb\NotifyCardGift
     */
    public function clearCode(){
      return $this->_clear(4);
    }
    
    /**
     * Get <code> value
     *
     * @return string
     */
    public function getCode(){
      return $this->_get(4);
    }
    
    /**
     * Set <code> value
     *
     * @param string $value
     * @return \brpb\NotifyCardGift
     */
    public function setCode( $value){
      return $this->_set(4, $value);
    }
  }
}
