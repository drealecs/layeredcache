<?php
namespace LayeredCache;

class CacheLayer {

    const SERIALIZATION_NONE = 0;
    const SERIALIZATION_JSON = 1;
    const SERIALIZATION_SERIALIZE = 2;
    
    const COMPRESSION_NONE = 0;
    const COMPRESSION_LZ4 = 1;
    const COMPRESSION_SNAPPY = 2;
    const COMPRESSION_LZF = 3;
    const COMPRESSION_GZ = 4;
    const COMPRESSION_BZIP2 = 5;

    /**
     * @var Backend\Cache
     */
    protected $cacheBackend;

    /**
     * @var int
     */
    protected $serialization;

    /**
     * @var int
     */
    protected $compression;
    
    protected static $allowedSerializations = array(self::SERIALIZATION_NONE, self::SERIALIZATION_JSON, self::SERIALIZATION_SERIALIZE);

    protected static $allowedCompressions = array(self::COMPRESSION_NONE, self::COMPRESSION_LZ4, self::COMPRESSION_SNAPPY, self::COMPRESSION_LZF, self::COMPRESSION_GZ, self::COMPRESSION_BZIP2);

    public function __construct($layerConfig, $serialization = self::SERIALIZATION_SERIALIZE)
    {
        if (!isset($layerConfig['backend'])) {
            throw new Exception('No backend specified');
        }
        $backendName = $layerConfig['backend'];
        if (!is_string($backendName)) {
            throw new Exception('Backend must be a string.');
        }
        $backendClass = 'Backend\\' . ucfirst($backendName);
        if(!class_exists($backendClass)) {
            throw new Exception("Unknown backend class: '{$backendClass}'.");
        }
        
        $this->cacheBackend = new $backendClass();
        
        $serialization = $layerConfig['serialization'];
        
        
        
        $this->serialization = $serialization;
    }

    /**
     * Serialize the data to be put into cache
     *
     * @param mixed $data
     * @return mixed
     */
    protected function serialize($data)
    {
        switch ($this->serialization) {
            case self::SERIALIZATION_JSON:
                $serializedData = json_encode($data);
                if (JSON_ERROR_NONE !== json_last_error()) {
                    throw new Exception('Error serialization with json_encode.');
                }
                break;
            case self::SERIALIZATION_SERIALIZE:
                $serializedData = serialize($data);
                if (!is_string($serializedData)) {
                    throw new Exception('Error serialization with serialize.');
                }
                break;
            default:
                $serializedData = $data;
        }
        return $serializedData;
    }
    
    /**
     * Unserialize the retrieved cached data
     *
     * @param mixed $data
     * @return mixed
     */
    protected function unserialize($data)
    {
        switch ($this->serialization) {
            case self::SERIALIZATION_JSON:
                $unserializedData = json_decode($data, true);
                if (JSON_ERROR_NONE !== json_last_error()) {
                    throw new Exception('Error unserialization with json_decode.');
                }
                break;
            case self::SERIALIZATION_SERIALIZE:
                $unserializedData = unserialize($data);
                if (false === $unserializedData && serialize(false) !== $data) {
                    throw new Exception('Error unserialization with unserialize.');
                }
                break;
            default:
                $unserializedData = $data;
        }
        return $unserializedData;
    }

    protected function compress($data)
    {
        return $data;
    }

    protected function uncompress($data)
    {
        return $data;
    }

    public function set($id, $data, $lifetime = null)
    {
        $this->cacheBackend->put($id, $data, $lifetime);
    }

}
