<?php
namespace LayeredCache\Tests\Backend;

use LayeredCache\Config;
use LayeredCache\Backend\Predis;
use LayeredCache\Tests\CacheBackendTest;

class PredisTest extends CacheBackendTest
{
    protected function getCacheBackend()
    {
	    $options = array(
            'host' => '127.0.0.1',
            'port' => 6379,
	    );
        return new Predis(new Config($options));
    }
	
	public function testFailed1MemcacheConstruct()
	{
		$this->setExpectedException('\LayeredCache\Exception');
		new Predis(array());
	}
	
	public function testFailed2MemcacheConstruct()
	{
		$this->setExpectedException('\LayeredCache\Exception');
		new Predis(array('host' => '127.0.0.1'));
	}
}
