<?php
namespace LayeredCache\Backend;

use LayeredCache\Exception;
use LayeredCache\Config;

class Memcache extends CommonAbstract implements Cache
{
    /**
     * @var \Memcache
     */
    private $memcache;

    protected function setup($config)
    {
        $this->memcache = new \Memcache();
        $servers = $config->servers;
        if ((!$servers instanceof Config) || count($servers) < 1) {
            throw new Exception('Memcache: no server specified in servers config');
        }
        foreach ($servers as $server) {
            if (is_null($server->host) || !is_string($server->host)) {
                throw new Exception('Memcache: invalid host specified for server');
            }
            if (is_null($server->port) || !is_numeric($server->port)) {
                throw new Exception('Memcache: invalid port specified for server');
            }
            @$this->memcache->addServer($server->host, (int)$server->port);
        }
    }
                
    public function put($id, $data, $lifeTime = 0)
    {
        return @$this->memcache->set($id, $data, 1 << 16, $lifeTime);
    }

    public function get($id)
    {
        $flags = null;
        $result = @$this->memcache->get($id, $flags);
        if ((false === $result) && (is_null($flags) || 1 !== ($flags >> 16))) {
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
        return false !== @$this->memcache->increment($id);
    }

    public function decrement($id)
    {
        return false !== @$this->memcache->decrement($id);
    }

    public function remove($id)
    {
        return (bool)@$this->memcache->delete($id);
    }

    public function flush()
    {
        return (bool)@$this->memcache->flush();
    }
}
