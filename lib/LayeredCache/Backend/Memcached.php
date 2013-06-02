<?php
namespace LayeredCache\Backend;

use LayeredCache\Exception;
use LayeredCache\Config;

class Memcached implements Cache
{
    /**
     * @var \Memcached
     */
    private $memcached;

    /**
     * @var Config
     */
    private $config;
                
    /**
     * @param Config $options
     */
    public function __construct($options)
    {
        if ($options instanceof Config) {
            $this->config = $options;
        } else {
            $this->config = new Config($options);
        }
        $this->setupMemcached();
    }
                
    /**
     * @throws \Exception
     */
    private function setupMemcached()
    {
        $this->memcached = new \Memcached();
        $servers = $this->config->servers;
        if ((!$servers instanceof Config) || count($servers) < 1) {
            throw new Exception('Memcached: no server specified in servers config');
        }
        foreach ($servers as $server) {
            if (is_null($server->host) || !is_string($server->host)) {
                throw new Exception('Memcached: invalid host specified for server');
            }
            if (is_null($server->port) || !is_numeric($server->port)) {
                throw new Exception('Memcached: invalid port specified for server');
            }
            @$this->memcached->addServer($server->host, (int)$server->port);
        }
    }
                
    public function put($id, $data, $lifeTime = 0)
    {
        return @$this->memcached->set($id, $data, $lifeTime);
    }

    public function get($id)
    {
        $flags = null;
        $result = @$this->memcached->get($id);
        if ((false === $result) && \Memcached::RES_NOTFOUND == $this->memcached->getResultCode()) {
            return null;
        }
        return $result;
    }

    public function contains($id)
    {
        return null !== $this->get($id);
    }

    public function increment($id)
    {
        return false !== @$this->memcached->increment($id);
    }

    public function decrement($id)
    {
        return false !== @$this->memcached->decrement($id);
    }

    public function remove($id)
    {
        return (bool)@$this->memcached->delete($id);
    }

    public function flush()
    {
        return (bool)@$this->memcached->flush();
    }
}
