<?php
namespace Yangyao\Lock;
abstract class Abs{

    protected $_key = null;

    protected $_id = null;

    protected $_fullKey = null;

    protected $_tries = 1;

    protected $_sleepForTry = 300;//100ms

    protected $_locked = false;

    protected $_lockExpire = 6;//6s
    
    protected $_role = null;

    public function __construct($id, $key, $expire=null, $addToPool=true, $tries=false){
        $this->_id = $id;
        $this->_key = $key;
        $this->_fullKey = $this->_genFullKey($this->_id, $this->_key);
        if (isset(Proc::$exists[$this->_fullKey])) { //如果在同一个进程已经上锁过相同的锁，就不重复上了
            return Proc::$exists[$this->_fullKey];
        }

        if (isIntNum($tries)) $this->_tries = $tries;

        if($expire && $expire>0){
            $this->_lockExpire = $expire;
        }

        $this->_init();
        Proc::$exists[$this->_fullKey] = $this;
        if ($addToPool) {
            Pool::share()->add($this);
        }
    }

    protected function _init(){
    }

    abstract protected function _genFullKey($id, $key);

    abstract public function unlock();
}