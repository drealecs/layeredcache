<?php
namespace LayeredCache;

use Traversable;

class Config implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    private $options = array();
    
    /**
     * @param array $options
     */
    public function __construct($options)
    {
        foreach ($options as $option => $optionValue) {
            $this->setOption($option, $optionValue);
        }
    }

    /**
     * @param string $option
     * @param mixed $value
     */
    private function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * @param string $option
     * @return mixed
     */
    private function getOption($option)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }
        return null;
    }
    
    public function __get($option)
    {
        $result = $this->getOption($option);
        if (is_array($result) || ($result instanceof Traversable)) {
            return new static($result);
        }
        return $result;
    }

    public function getIterator()
    {
        $options = array();
        foreach (array_keys($this->options) as $optionName) {
            $options[$optionName] = $this->__get($optionName);
        }
        return new \ArrayIterator($options);
    }
    
    public function count()
    {
        return count($this->options);
    }
}
