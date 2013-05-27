<?php
namespace LayeredCache;

class Cache
{
    protected $stacks = array();
    
    public function __get($stack)
    {
        if (isset($this->stacks[$stack])) {
            return $this->stacks[$stack];
        }
        throw new Exception('Undefined Stack: ' . $stack);
    }
}
