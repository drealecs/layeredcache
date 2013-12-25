<?php
namespace LayeredCache\Backend;

use LayeredCache\Exception;
use Predis\Client as PredisClient;
use Predis\ResponseErrorInterface;

class Predis extends CommonAbstract implements Cache, TaggableCache
{

    /**
     * @var PredisClient
     */
    private $redis;

    protected function setup($config)
    {
        $host = $config->host;
        if (is_null($host) || !is_string($host)) {
            throw new Exception('Predis: invalid host specified for server');
        }
        $port = $config->port;
        if (is_null($port) || !is_numeric($port)) {
            throw new Exception('Memcache: invalid port specified for server');
        }
        $database = $config->database;
        if (is_null($database) || !is_numeric($database)) {
            $database = 0;
        }
        $this->redis = new PredisClient(array(
            'scheme' => 'tcp',
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'connection_timeout' => 1,
        ));
    }
    
    /**
     * @param string $id The id of the cache entry to fetch.
     * @return string|null The cached data or null, if no cache entry exists for the given id.
     */
    public function get($id)
    {
        $command = $this->redis->createCommand('get', array($id));
        $response = $this->redis->executeCommand($command);
        if ($response instanceof ResponseErrorInterface) {
            return null;
        }

        return $response;
    }

    /**
     * @param string $id The cache id of the entry to check for.
     * @return boolean true or false if a cache entry exists or not for the given cache id.
     */
    public function contains($id)
    {
        $command = $this->redis->createCommand('exists', array($id));
        $response = $this->redis->executeCommand($command);
        if ($response instanceof ResponseErrorInterface) {
            return false;
        }

        return (bool)$response;
    }

    /**
     * @param string $id The cache id.
     * @param string|array $data The cache data.
     * @param int $lifeTime The cache lifetime in seconds (0 => infinite).
     * @return boolean true if the entry was successfully stored, false otherwise.
     */
    public function put($id, $data, $lifeTime = 0)
    {
        if (0 === $lifeTime) {
            $command = $this->redis->createCommand('set', array($id, $data));
        } else {
            $command = $this->redis->createCommand('setex', array($id, $lifeTime, $data));
        }
        $response = $this->redis->executeCommand($command);
        if ($response instanceof ResponseErrorInterface) {
            return false;
        }

        return (bool)$response;
    }

    /**
     * @param string $id The cache id.
     * @return boolean true if the entry was successfully incremented, false otherwise.
     */
    public function increment($id)
    {
        if (!$this->contains($id)) {
            return false;
        }
        $command = $this->redis->createCommand('incr', array($id));
        $response = $this->redis->executeCommand($command);
        if ($response instanceof ResponseErrorInterface) {
            return false;
        }

        return true;
    }

    /**
     * @param string $id The cache id.
     * @return boolean true if the entry was successfully decremented, false otherwise.
     */
    public function decrement($id)
    {
        if (!$this->contains($id)) {
            return false;
        }
        $command = $this->redis->createCommand('decr', array($id));
        $response = $this->redis->executeCommand($command);
        if ($response instanceof ResponseErrorInterface) {
            return false;
        }

        return true;
    }

    /**
     * @param string $id The cache id.
     * @return boolean true if the entry was successfully deleted, false otherwise.
     */
    public function remove($id)
    {
        if (!$this->contains($id)) {
            return false;
        }
        $command = $this->redis->createCommand('del', array($id));
        $response = $this->redis->executeCommand($command);
        if ($response instanceof ResponseErrorInterface) {
            return false;
        }

        return true;
    }

    /**
     * @return boolean true if the entry was successfully flushed, false otherwise.
     */
    public function flush()
    {
        $command = $this->redis->createCommand('flushdb');
        return $this->redis->executeCommand($command);
    }

    /**
     * @param string $id The cache id.
     * @param string|array $data The cache data.
     * @param int $lifeTime The cache lifetime in seconds (0 => infinite).
     * @param string[] $tags A set of tags for the cache.
     * @return boolean true if the entry was successfully stored, false otherwise.
     */
    public function putWithTags($id, $data, $tags = array(), $lifeTime = 0)
    {
        $putResult = $this->put($id, $data, $lifeTime);
        if ($putResult) {
            $setTagsResult = $this->setTags($id, $tags);
            if ($setTagsResult) {
                return true;
            }
            $this->remove($id);
        }
        return false;
    }

    /**
     * @param string $id The cache id.
     * @param string[] $tags A set of tags to be added for the cache
     * @return bool
     */
    public function addTags($id, array $tags)
    {
        $transaction = $this->redis->multiExec();
        
        $commandArgs = $tags;
        array_unshift($commandArgs, $this->getTagsForIdKey($id));
        $command = $this->redis->createCommand('sadd', $commandArgs);
        $transaction->executeCommand($command);
        foreach ($tags as $tag) {
            $command = $this->redis->createCommand('sadd', array($this->getIdsForTagKey($tag), $id));
            $transaction->executeCommand($command);
        }
        $responses = $transaction->exec();
        foreach ($responses as $response) {
            if ($response instanceof ResponseErrorInterface) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * @param string $id
     * @param string[] $tags
     * @return bool
     */
    public function removeTags($id, array $tags)
    {
        if (count($tags) > 0) {
            $transaction = $this->redis->multiExec();
    
            $commandArgs = $tags;
            array_unshift($commandArgs, $this->getTagsForIdKey($id));
            $command = $this->redis->createCommand('srem', $commandArgs);
            $transaction->executeCommand($command);
            foreach ($tags as $tag) {
                $command = $this->redis->createCommand('srem', array($this->getIdsForTagKey($tag), $id));
                $transaction->executeCommand($command);
            }
            $responses = $transaction->exec();
            foreach ($responses as $response) {
                if ($response instanceof ResponseErrorInterface) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param string $id The cache id.
     * @param string[] $tags
     * @return bool
     */
    public function setTags($id, array $tags)
    {
        $existingTags = $this->getTags($id);
        $tagsToAdd = array_diff($tags, $existingTags);
        $tagsToRemove = array_diff($existingTags, $tags);
        $addResult = $this->addTags($id, $tagsToAdd);
        $removeResult = $this->removeTags($id, $tagsToRemove);
        return $addResult && $removeResult;
    }

    /**
     * @param string $id The cache id.
     * @return string[] The set of tags associated with the cache
     */
    public function getTags($id)
    {
        $command = $this->redis->createCommand('smembers', array($this->getTagsForIdKey($id)));
        $response = $this->redis->executeCommand($command);
        if ($response instanceof ResponseErrorInterface) {
            return null;
        }

        return $response;
    }

    /**
     * @param string[] $tags
     * @return string[] Ids
     */
    public function getIdsByTags(array $tags)
    {
        $command = $this->redis->createCommand('sinter', $this->getIdsForTagKeys($tags));
        $response = $this->redis->executeCommand($command);
        if ($response instanceof ResponseErrorInterface) {
            return null;
        }
        
        return $response;
    }

    /**
     * @param string[] $tags
     * @return string[] Ids
     */
    public function getIdsByAnyTags(array $tags)
    {
        $command = $this->redis->createCommand('sunion', $this->getIdsForTagKeys($tags));
        $response = $this->redis->executeCommand($command);
        if ($response instanceof ResponseErrorInterface) {
            return null;
        }

        return $response;
    }

    /**
     * @param string[] $tags
     * @return bool
     */
    public function clearByTags(array $tags)
    {
        foreach ($this->getIdsByTags($tags) as $id) {
            $this->removeTags($id, $this->getTags($id));
            $this->remove($id);
        }
        return true;
    }

    /**
     * @param string[] $tags
     * @return bool
     */
    public function clearByAnyTags(array $tags)
    {
        foreach ($this->getIdsByAnyTags($tags) as $id) {
            $this->removeTags($id, $this->getTags($id));
            $this->remove($id);
        }
        return true;
    }
    
    
    private function getTagsForIdKey($id) {
        return 'tagsForId:' . $id;
    }

    private function getIdsForTagKey($tag) {
        return 'idsForTag:' . $tag;
    }

    private function getIdsForTagKeys($tags) {
        $result = array();
        foreach ($tags as $tag) {
            $result[] = $this->getIdsForTagKey($tag);
        }
        return $result;
    }
    
}
