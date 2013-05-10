<?php
namespace LayeredCache\Backend;

interface Cache
{
    /**
     * @param string $id The id of the cache entry to fetch.
     * @return string|null The cached data or null, if no cache entry exists for the given id.
     */
    function get($id);

    /**
     * @param string $id The cache id of the entry to check for.
     * @return boolean true or false if a cache entry exists or not for the given cache id.
     */
    function contains($id);

    /**
     * @param string $id The cache id.
     * @param string|array $data The cache data.
     * @param int $lifeTime The cache lifetime in seconds (0 => infinite).
     * @return boolean true if the entry was successfully stored, false otherwise.
     */
    function put($id, $data, $lifeTime = 0);

    /**
     * @param string $id The cache id.
     * @return boolean true if the entry was successfully incremented, false otherwise.
     */
    function increment($id);

    /**
     * @param string $id The cache id.
     * @return boolean true if the entry was successfully decremented, false otherwise.
     */
    function decrement($id);

    /**
     * @param string $id The cache id.
     * @return boolean true if the entry lifetime was successfully reset, false otherwise.
     */
    function touch($id);
    
    /**
     * @param string $id The cache id.
     * @return boolean true if the entry was successfully deleted, false otherwise.
     */
    function remove($id);
    
    /**
     * @return boolean true if the entry was successfully flushed, false otherwise.
     */
    function flush();
    
    /**
     * @return array
     */
    function getCapabilities();
}
