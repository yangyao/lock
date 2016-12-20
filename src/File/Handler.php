<?php
namespace Yangyao\Lock\File;
class Handler{

    private $storePath = null;
    const  LOCK_FILE_PATH = '/tmp/lock';

    function __construct() {
        $this->_storePath = self::LOCK_FILE_PATH;
    }
    /*
     * 获取到锁则返回TRUE； 如果文件锁过期则删除
     */
    public function get($fullKey) {
        $file = $this->_storePath . $fullKey;
        $this->_clearExpired($file);
        if (is_file($file)) {
            return true;
        }
        return false;
    }

    /*
     * 生成锁则返回TRUE； 如果文件锁过期则删除
     */
    public function set($fullKey, $expire) {
        $file = $this->_storePath . $fullKey;
        $this->_clearExpired($file);
        if (is_file($file)) {
            return false;
        }
        if (false !== file_put_contents($file, time() + $expire)) {
            return true;
        }
        return false;
    }

    public function delete($fullKey) {
        $file = $this->_storePath . $fullKey;
        unlink($file);
    }

    private function _clearExpired($file) {
        if (is_file($file)) {
            $expireUp = file_get_contents($file);
            if ($expireUp && strlen($expireUp)==10 && $expireUp <= time()) {
                unlink($file);
            }
        }
    }
}