<?php
namespace Yangyao\Lock;
use Exception;
class File extends Abs{

    protected $_handler;

    public static function isLocked($id, $key) {
        $fullKey = self::selfFullKey($id, $key);
        $handler = new File\Handler();
        if ($handler->get($fullKey)) {
            return true;
        }
        return false;
    }

    protected static function selfFullKey($id, $key) {
        return $id. '.' . $key;
    }

    public function _init(){
        $i = 0;
        $this->_handler = new File\Handler();
        do{
            while ($i<$this->_tries){
                $locked = $this->_handler->get($this->_fullKey);
                if(!$locked){
                    break 2;
                }
                $i++;
                if ($i<$this->_tries) usleep($this->_sleepForTry);
            }
            //$this->_locked = false;
            throw new Exception(__METHOD__. "{$this->_id}|lock:{$this->_key} failed because existed");
        }while(false);
        if (!$this->_handler->set($this->_fullKey, $this->_lockExpire)) {
            throw new Exception(__METHOD__. "{$this->_id}|lock:{$this->_key} failed when set");
        }
        $this->_locked = true;
    }

    protected function _genFullKey($id, $key) {
        return self::selfFullKey($id, $key);
    }

    public function unlock(){
        if($this->_locked){
            $this->_handler->delete($this->_fullKey);
        }
    }
}