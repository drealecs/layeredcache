<?php

namespace LayeredCache\Backend;

use LayeredCache\Config;
use LayeredCache\Exception;

abstract class CommonAbstract
{

    /**
     * @param Config|array $options
     * @throws \LayeredCache\Exception
     */
    public function __construct($options)
    {
        if ($options instanceof Config) {
            $config = $options;
        } elseif (is_array($options)) {
            $config = new Config($options);
        } else {
            throw new Exception('Invalid configuration');
        }

        $this->setup($config);
    }

    /**
     * @param $config
     * @throws Exception
     */
    abstract protected function setup($config);

}