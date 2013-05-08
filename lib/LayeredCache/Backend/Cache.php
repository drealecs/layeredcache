<?php
namespace LayeredCache\Backend;

interface Cache
{
    /**
     * Fetches an entry from the cache.
     *
     * @param string $id The id of the cache entry to fetch.
     * @return string|null The cached data or null, if no cache entry exists for the given id.
     */
    function get($id);

    /**
     * Tests if an entry exists in the cache.
     *
     * @param string $id The cache id of the entry to check for.
     * @return boolean true or false if a cache entry exists or not for the given cache id.
     */
    function contains($id);

    /**
     * Puts data into the cache.
     *
     * @param string $id The cache id.
     * @param string|array $data The cache data.
     * @param int $lifeTime The cache lifetime in seconds (0 => infinite).
     * @return boolean true if the entry was successfully stored, false otherwise.
     */
    function put($id, $data, $lifeTime = 0);

    /**
     * Increment a cache entry
     *
     * @param string $id The cache id.
     * @return boolean true if the entry was successfully incremented, false otherwise.
     */
    function increment($id);

    /**
     * Decrement a cache entry
     *
     * @param string $id The cache id.
     * @return boolean true if the entry was successfully decremented, false otherwise.
     */
    function decrement($id);

    /**
     * Reset lifetime of a cache entry
     *
     * @param string $id The cache id.
     * @return boolean true if the entry lifetime was successfully reset, false otherwise.
     */
    function touch($id);
    
    /**
     * Deletes a cache entry.
     *
     * @param string $id The cache id.
     * @return boolean true if the entry was successfully deleted, false otherwise.
     */
    function remove($id);
    
    /**
     * Delete all cache entries
     * 
     * @return boolean true if the entry was successfully flushed, false otherwise.
     */
    function flush();
    
    /**
     * Get the backend capabilities
     * 
     * @return array
     */
    function getCapabilities();
}
