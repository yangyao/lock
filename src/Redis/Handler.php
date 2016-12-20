<?php
namespace Yangyao\Lock\Redis;
use Predis\Client;
use Exception;
class Handler{
    /**
     * The scene clients instance.
     *
     * @var array
     */
    protected $scene;

    /**
     * The clients instance.
     *
     * @var array
     */
    protected $client;
    /**
     * The redis connection
     *
     * @var array
     */
    protected $connection = 'lock';


    function __construct() {
        $this->connection();
    }

    /**
     * Get a specific Redis connection instance.
     *
     * @param  string  $name
     * @return \Predis\ClientInterface|null
     */
    protected function connection($options = [])
    {
        if (isset($this->client)) return $this->client;

        if (is_null($connectionConfig = config::get('redis.connections.'.$this->connection)))
        {
            throw new Exception("Redis connection [$this->connection] is not defined");
        }

        $servers = (array) array_get($connectionConfig, 'servers');

        $configOptions = (array) array_get($connectionConfig, 'options');

        $options = $options + $configOptions;

        // 如果存在多个server配置, 默认为集群
        if (isset($servers[1]))
        {
            return $this->client = new Client($servers, $options);
        }
        else
        {
            return $this->client = new Client(array_pop($servers), $options);
        }
    }

    public function get($fullKey) {
        return $this->client->get($fullKey);
    }

    public function set($fullKey, $expire) {
        //这个$expire 秒数不能超过60×60×24×30（30天时间的秒数）;如果失效的值大于这个值， 服务端会将其作为一个真实的Unix时间戳来处理而不是 自当前时间的偏移。
        return $this->client->pipeline()->set($fullKey, 1)->expire($fullKey, $expire)->execute();
       // return $this->client->set($fullKey, 1, $expire);
    }

    public function delete($fullKey) {
        return $this->client->del($fullKey);
    }
}