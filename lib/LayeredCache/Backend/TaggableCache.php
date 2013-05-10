<?php
namespace LayeredCache\Backend;

interface TaggableCache extends Cache
{
    /**
     * Puts data into the cache.
     *
     * @param string $id The cache id.
     * @param string|array $data The cache data.
     * @param int $lifeTime The cache lifetime in seconds (0 => infinite).
     * @param string[] $tags A set of tags for the cache.
     * @return boolean true if the entry was successfully stored, false otherwise.
     */
    function put($id, $data, $lifeTime = 0, $tags = array());

    /**
     * Set tags to an item by given key.
     * An empty array will remove all tags.
     *
     * @param string $id The cache id.
     * @param string[] $tags A set of tags to be added for the cache
     * @return bool
     */
    public function addTags($id, array $tags);

    /**
     * Set tags to an item by given key.
     * An empty array will remove all tags.
     *
     * @param string $id The cache id.
     * @param string[] $tags A set of tags to be removed for the cache
     * @return bool
     */
    public function removeTags($id, array $tags);

    /**
     * Get tags of an item by given key
     *
     * @param string $id The cache id.
     * @return string[] The set of tags associated with the cache
     */
    public function getTags($id);

    /**
     * Remove items matching all given tags.
     *
     * @param string[] $tags
     * @return bool
     */
    public function clearByTags(array $tags);

    /**
     * Remove items matching any given tags.
     *
     * @param string[] $tags
     * @return bool
     */
    public function clearByAnyTags(array $tags);

}
