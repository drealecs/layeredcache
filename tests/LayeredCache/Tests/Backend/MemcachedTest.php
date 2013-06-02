<?php
namespace LayeredCache\Tests\Backend;

use LayeredCache\Config;
use LayeredCache\Backend\Memcached;
use LayeredCache\Tests\CacheBackendTest;

class MemcachedTest extends CacheBackendTest
{
    protected function getCacheBackend()
    {
	    $options = array(
		    'servers' => array(
			    array('host' => '127.0.0.1', 'port' => 11211),
		    ),
	    );
        return new Memcached(new Config($options));
    }
	
	public function testFailed1MemcacheConstruct()
	{
		$this->setExpectedException('\LayeredCache\Exception');
		new Memcached(array());
	}
	
	public function testFailed2MemcacheConstruct()
	{
		$this->setExpectedException('\LayeredCache\Exception');
		new Memcached(array('servers' => array(array())));
	}
	
	public function testFailed3MemcacheConstruct()
	{
		$this->setExpectedException('\LayeredCache\Exception');
		new Memcached(array('servers' => array(array('host' => '127.0.0.1'))));
	}
}
