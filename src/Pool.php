<?php

namespace Yangyao\Lock;
class Pool{
    protected $_stack = array();

    private static $instance = array();

    public static function share(){
        if (!isset(self::$instance[0]) || empty(self::$instance[0])) {
            self::$instance[0] = new self();
        }
        return self::$instance[0];
    }

    public function add(Abs $lock){
        array_unshift($this->_stack, $lock);
    }

    public function release(){
        foreach($this->_stack as $lock){
            $lock->unlock();
        }
    }
}