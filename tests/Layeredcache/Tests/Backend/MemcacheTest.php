<?php
namespace LayeredCache\Tests\Backend;

use LayeredCache\Config;
use LayeredCache\Backend\Memcache;
use LayeredCache\Tests\CacheBackendTest;

class MemcacheTest extends CacheBackendTest
{
    protected function getCacheBackend()
    {
	    $options = array(
		    'servers' => array(
			    array('host' => '127.0.0.1', 'port' => 11211),
		    ),
	    );
        return new Memcache(new Config($options));
    }
	
	public function testFailed1MemcacheConstruct()
	{
		$this->setExpectedException('\LayeredCache\Exception');
		new Memcache(array());
	}
	
	public function testFailed2MemcacheConstruct()
	{
		$this->setExpectedException('\LayeredCache\Exception');
		new Memcache(array('servers' => array(array())));
	}
	
	public function testFailed3MemcacheConstruct()
	{
		$this->setExpectedException('\LayeredCache\Exception');
		new Memcache(array('servers' => array(array('host' => '127.0.0.1'))));
	}
}
