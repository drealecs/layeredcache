<?php
namespace LayeredCache\Backend;

use LayeredCache\Exception;

class Memcache implements Cache
{
    /**
     * @var \Memcache
     */
    private $memcache;

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
        $this->setupMemcache();
    }
    
    /**
     * @throws \Exception
     */
    private function setupMemcache()
    {
        $this->memcache = new \Memcache();
        $servers = $this->config->getOption('servers');
        if (!is_array($servers) || count($servers) < 1) {
            throw new Exception('Memcache: no server specified in servers config');
        }
        foreach ($servers as $server) {
            if (!isset($server['host']) || !is_string($server['host'])) {
                throw new Exception('Memcache: invalid host specified for server');
            }
            if (!isset($server['port']) || !is_numeric($server['port'])) {
                throw new Exception('Memcache: invalid port specified for server');
            }
            $addResult = @$this->memcache->addServer($server['host'], (int)$server['port']);
            if (!$addResult) {
                throw new Exception('Memcache: unable to add server ' . $server['host'] . ':' . $server['port']);
            }
        }
    }
    
    function put($id, $data, $lifeTime = 0)
    {
        return @$this->memcache->set($id, $data, 65536, $lifeTime);
    }
    
    function get($id)
    {
        $flags = null;
        $result = @$this->memcache->get($id, $flags);
        if ((false === $result) && (is_null($flags) || 1 !== ($flags >> 16))) {
            return null;
        }
        return $result;
    }
    
    function contains($id)
    {
        return null !== $this->get($id);
    }

    function increment($id)
    {
        return false !== @$this->memcache->increment($id);
    }
    
    function decrement($id)
    {
        return false !== @$this->memcache->decrement($id);
    }

    function remove($id)
    {
        return (bool)@$this->memcache->delete($id);
    }

    function flush()
    {
        return (bool)@$this->memcache->flush();
    }

}
