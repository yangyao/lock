<?php
namespace Yangyao\Lock\Memcache;
use Memcached;
use Exception;
class Handler{

    private static $mcApi = null;

    function __construct($fullKey) {
        $this->connect();
    }

    private function connect()
    {
        if(!isset(self::$mcApi)){
            //TODO-PENDING 这里需要写在配置中
            $hosts = (array)config::get('kvstore.base_kvstore_memcached.hosts');
            if(!empty($hosts)){
                self::$mcApi = new Memcached;
                //TODO-NOTICE 连接阿里云memcache需要修改这里
                self::$mcApi->setOption(Memcached::OPT_COMPRESSION, false); //关闭压缩功能
                self::$mcApi->setOption(Memcached::OPT_BINARY_PROTOCOL, true); //使用binary二进制协议
                foreach($hosts AS $row){
                    $row = trim($row);
                    if(strpos($row, 'unix:///') === 0){
                    }else{
                        $tmp = explode(':', $row);
                        self::$mcApi->addServer($tmp[0], $tmp[1]);
                    }
                }
            }else{
                throw new Exception(__METHOD__."lock:base_lock_memcache_handler hosts is empty, please check it");
            }
        }
    }

    public function get($fullKey) {
        return self::$mcApi->get($fullKey);
    }

    public function set($fullKey, $expire) {
        //这个$expire 秒数不能超过60×60×24×30（30天时间的秒数）;如果失效的值大于这个值， 服务端会将其作为一个真实的Unix时间戳来处理而不是 自当前时间的偏移。
        return self::$mcApi->set($fullKey, 1, $expire);
    }

    public function delete($fullKey) {
        return self::$mcApi->delete($fullKey);
    }
}