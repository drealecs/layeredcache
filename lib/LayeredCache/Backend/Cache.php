<?php
namespace LayeredCache\Backend;

interface Cache
{
    /**
     * @param string $id The id of the cache entry to fetch.
     * @return string|null The cached data or null, if no cache entry exists for the given id.
     */
    public function get($id);

    /**
     * @param string $id The cache id of the entry to check for.
     * @return boolean true or false if a cache entry exists or not for the given cache id.
     */
    public function contains($id);

    /**
     * @param string $id The cache id.
     * @param string|array $data The cache data.
     * @param int $lifeTime The cache lifetime in seconds (0 => infinite).
     * @return boolean true if the entry was successfully stored, false otherwise.
     */
    public function put($id, $data, $lifeTime = 0);

    /**
     * @param string $id The cache id.
     * @return boolean true if the entry was successfully incremented, false otherwise.
     */
    public function increment($id);

    /**
     * @param string $id The cache id.
     * @return boolean true if the entry was successfully decremented, false otherwise.
     */
    public function decrement($id);

    /**
     * @param string $id The cache id.
     * @return boolean true if the entry was successfully deleted, false otherwise.
     */
    public function remove($id);
    
    /**
     * @return boolean true if the entry was successfully flushed, false otherwise.
     */
    public function flush();
}
