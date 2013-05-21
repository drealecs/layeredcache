<?php
namespace LayeredCache\Backend;

class Config
{
    /**
     * @var array
     */
    private $options;
    
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
    public function getOption($option)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }
    }
}
