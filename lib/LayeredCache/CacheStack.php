<?php
namespace LayeredCache;

class CacheStack
{
    /**
     * @var CacheLayer[] Array of cache layers
     */
    protected $layers = array();

    /**
     * @param $stackConfig array of layers config
     */
    public function __construct($stackConfig)
    {
        foreach ($stackConfig as $layerConfig) {
            $this->layers[] = new CacheLayer($layerConfig);
        }
    }
    
    public function set($id, $data, $lifetime = null)
    {
        foreach ($this->layers as $layer) {
            $layer->set($id, $data, $lifetime);
        }
    }
}
