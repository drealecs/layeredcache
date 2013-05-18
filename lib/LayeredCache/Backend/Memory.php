<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 5/11/13
 * Time: 7:48 AM
 * To change this template use File | Settings | File Templates.
 */

namespace LayeredCache\Backend;


use string;

class Memory implements Cache, TaggableCache
{
    /**
     * @var array
     */
    private $data = array();

    /**
     * @var array
     */
    private $tags = array();

    public function get($id)
    {
        $element = $this->checkAndGetElement($id);
        if (null !== $element) {
            return $element['data'];
        }
    }

    public function contains($id)
    {
        return null !== $this->checkAndGetElement($id);
    }

    public function put($id, $data, $lifeTime = 0)
    {
        if (0 === $lifeTime) {
            $expire = 0;
        } else {
            $expire = time() + $lifeTime;
        }
        $this->data[$id] = array('data' => $data, 'expire' => $expire, 'tags' => array());
        return true;
    }

    public function increment($id)
    {
        if (null !== $this->checkAndGetElement($id)) {
            $this->data[$id]['data']++;
            return true;
        }
        return false;
    }

    public function decrement($id)
    {
        if (null !== $this->checkAndGetElement($id)) {
            $this->data[$id]['data']--;
            return true;
        }
        return false;
    }

    public function remove($id)
    {
        if (isset($this->data[$id])) {
            $this->removeTags($id, $this->data[$id]['tags']);
            unset($this->data[$id]);
            return true;
        }
        return false;
    }

    public function flush()
    {
        $this->data = array();
        $this->tags = array();
    }
    
    private function checkAndGetElement($id)
    {
        if (isset($this->data[$id])) {
            if ((0 === $this->data[$id]['expire']) ||  ($this->data[$id]['expire'] >= time())) {
                return $this->data[$id];
            } else {
                $this->remove($id);
            }
        }
        return null;
    }

    public function putWithTags($id, $data, $tags = array(), $lifeTime = 0)
    {
        $this->put(id, $data, $lifeTime);
        $this->setTags($id, $tags);
    }

    public function addTags($id, array $tags)
    {
        foreach ($tags as $tag) {
            $this->tags[$tag][$id] = true;
            $this->data[$id]['tags'][$tag] = $tag;
        }
    }

    public function removeTags($id, array $tags)
    {
        $existingTags = array_intersect($tags, $this->data[$id]['tags']);
        foreach ($existingTags as $tag) {
            unset($this->tags[$tag][$id]);
            unset($this->data[$id]['tags'][$tag]);
        }
    }

    public function setTags($id, array $tags)
    {
    }

    public function getTags($id)
    {
        return $this->data[$id]['tags'];
    }

    public function clearByTags(array $tags)
    {
    }

    public function clearByAnyTags(array $tags)
    {
    }
}
