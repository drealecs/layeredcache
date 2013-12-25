<?php
namespace LayeredCache\Tests\Backend;

use LayeredCache\Backend\Apc;
use LayeredCache\Tests\CacheBackendTest;

class ApcTest extends CacheBackendTest
{
    protected function getCacheBackend()
    {
        return new Apc();
    }
}
