<?php
namespace LayeredCache\Tests;

use LayeredCache\Backend\TaggableCache;

abstract class CacheBackendTest extends LayeredCacheTest
{
    /**
     * @return \LayeredCache\Backend\Cache
     */
    abstract protected function getCacheBackend();
    
    public function testPutGetRemove()
    {
        $cacheBackend = $this->getCacheBackend();
	    
        $this->assertTrue($cacheBackend->put('test1', 'common test text'));
        $this->assertTrue($cacheBackend->contains('test1'));
        $this->assertEquals('common test text', $cacheBackend->get('test1'));
        $this->assertTrue($cacheBackend->remove('test1'));
        $this->assertFalse($cacheBackend->contains('test1'));
        $this->assertNull($cacheBackend->get('test1'));
	    $this->assertFalse($cacheBackend->remove('test1'));
    }
    
    public function testPutGetWithExpire()
    {
        $cacheBackend = $this->getCacheBackend();
        
        $this->assertTrue($cacheBackend->put('test2', 'expire test text', 1));
        $this->assertTrue($cacheBackend->contains('test2'));
        $this->assertEquals('expire test text', $cacheBackend->get('test2'));
        sleep(2);
        $this->assertFalse($cacheBackend->contains('test2'));
        $this->assertNull($cacheBackend->get('test2'));
    }
    
    public function testFlush()
    {
        $cacheBackend = $this->getCacheBackend();

        $cacheBackend->put('test1', 'test1 text');
        $cacheBackend->put('test2', 'test2 text');
        $cacheBackend->put('test3', 'test3 text');
        $cacheBackend->flush();
        $this->assertFalse($cacheBackend->contains('test1'));
        $this->assertFalse($cacheBackend->contains('test2'));
        $this->assertFalse($cacheBackend->contains('test3'));
    }
    
    public function testIncrementAndDecrement()
    {
        $cacheBackend = $this->getCacheBackend();
        
        $cacheBackend->put('test3', 4);
        $this->assertTrue($cacheBackend->increment('test3'));
        $this->assertEquals(5, $cacheBackend->get('test3'));
        $this->assertTrue($cacheBackend->decrement('test3'));
        $cacheBackend->decrement('test3');
        $this->assertEquals(3, $cacheBackend->get('test3'));
	    $cacheBackend->remove('test3');
	    $this->assertFalse($cacheBackend->increment('test3'));
	    $this->assertFalse($cacheBackend->decrement('test3'));
    }
	
	public function testSetGetTags()
	{
		$cacheBackend = $this->getCacheBackend();
		if (!$cacheBackend instanceof TaggableCache) {
			return;
		}

		$this->assertTrue($cacheBackend->putWithTags('test4', 'tags string cache', array('tag1', 'tag2')));
		$this->assertContains('tag1', $cacheBackend->getTags('test4'));
		$this->assertContains('tag2', $cacheBackend->getTags('test4'));
		$cacheBackend->putWithTags('test5', 'tags string cache', array('tag1', 'tag3'));
		$this->assertContains('test4', $cacheBackend->getIdsByTags(array('tag1', 'tag2')));
		$this->assertEmpty($cacheBackend->getIdsByTags(array('tag2', 'tag3')));
		$this->assertCount(2, $cacheBackend->getIdsByAnyTags(array('tag1', 'tag2', 'tag3')));
		$this->assertTrue($cacheBackend->clearByTags(array('tag1', 'tag2', 'tag3')));
		$this->assertCount(2, $cacheBackend->getIdsByAnyTags(array('tag1', 'tag2', 'tag3')));
		$this->assertTrue($cacheBackend->clearByTags(array('tag1', 'tag3')));
		$this->assertCount(1, $cacheBackend->getIdsByAnyTags(array('tag1', 'tag2', 'tag3')));
		$this->assertTrue($cacheBackend->clearByAnyTags(array('tag1', 'tag3')));
		$this->assertCount(0, $cacheBackend->getIdsByAnyTags(array('tag1', 'tag2', 'tag3')));
	}
    
}
