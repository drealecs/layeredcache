<?php
namespace LayeredCache\Tests\Backend;

use LayeredCache\Backend\Memory;
use LayeredCache\Tests\CacheBackendTest;

class MemoryTest extends CacheBackendTest
{
    protected function getCacheBackend()
    {
        return new Memory();
    }
}
