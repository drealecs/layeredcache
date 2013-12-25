<?php
namespace LayeredCache\Backend;

class Apc implements Cache
{

    public function get($id)
    {
        $result = apc_fetch($id, $success);
        if (false === $result && false === $success) {
            return null;
        }
        return $result;
    }

    public function contains($id)
    {
        return apc_exists($id);
    }

    public function put($id, $data, $lifeTime = 0)
    {
        return apc_store($id, $data, $lifeTime);
    }

    public function increment($id)
    {
        apc_inc($id, 1, $success);
        return $success;
    }

    public function decrement($id)
    {
        apc_dec($id, 1, $success);
        return $success;
    }

    public function remove($id)
    {
        return apc_delete($id);
    }

    public function flush()
    {
        return apc_clear_cache('user');
    }
}