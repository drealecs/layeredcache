<?php
namespace LayeredCache;

class CacheLayer
{
    const SERIALIZATION_NONE = 0;
    const SERIALIZATION_JSON = 1;
    const SERIALIZATION_SERIALIZE = 2;
        
    const COMPRESSION_NONE = 0;
    const COMPRESSION_LZ4 = 1;
    const COMPRESSION_SNAPPY = 2;
    const COMPRESSION_LZF = 3;
    const COMPRESSION_GZ = 4;
        
    /**
     * @var Backend\Cache
     */
    protected $cacheBackend;
        
    /**
     * @var int default cache lifetime
     */
    protected $lifetime = 0;
        
    /**
     * @var int
     */
    protected $serialization;
        
    /**
     * @var int
     */
    protected $compression;
        
    protected static $allowedSerializations = array(
        self::SERIALIZATION_NONE,
        self::SERIALIZATION_JSON,
        self::SERIALIZATION_SERIALIZE
    );
        
    protected static $allowedCompressions = array(
        self::COMPRESSION_LZ4,
        self::COMPRESSION_SNAPPY,
        self::COMPRESSION_LZF,
        self::COMPRESSION_GZ,
        self::COMPRESSION_NONE
    );
        
    protected static $filteredAllowedCompressions = false;
        
    /**
     * @param Config $layerConfig
     * @throws Exception
     */
    public function __construct($layerConfig)
    {
        if (!$layerConfig instanceof Config) {
            $layerConfig = new Config($layerConfig);
        }
        if (is_null($layerConfig->backend)) {
            throw new Exception('No backend specified');
        }
        $backend = $layerConfig->backend;
        if (is_string($backend)) {
            $backendClass = 'Backend\\' . ucfirst($backend);
            if (!class_exists($backendClass)) {
                throw new Exception("Unknown backend class: '{$backendClass}'.");
            }
            $backendOptions = $layerConfig->backendOptions;
            $this->cacheBackend = new $backendClass($backendOptions);
            if (!($this->cacheBackend instanceof Backend\Cache)) {
                throw new Exception('Backend created is not instance of Backend\Cache');
            }
        } elseif ($backend instanceof Backend\Cache) {
            $this->cacheBackend = $backend;
        } else {
            throw new Exception('Backend must be a string or an instance of Backend\Cache');
        }
                
        if (!is_null($layerConfig->lifetime)) {
            $this->lifetime = $layerConfig->lifetime;
        }
                
        if (!is_null($layerConfig->serialization)) {
            $serialization = $layerConfig->serialization;
            if (!in_array($serialization, self::$allowedSerializations)) {
                throw new Exception('Not available serialization in config');
            }
            $this->serialization = $serialization;
        } else {
            $this->serialization = self::SERIALIZATION_SERIALIZE;
        }
                
        self::filterAllowedCompressions();
        if (!is_null($layerConfig->compression)) {
            $compression = $layerConfig->compression;
            if (!in_array($compression, self::$allowedCompressions)) {
                throw new Exception('Not available compression in config');
            }
            $this->compression = $compression;
        } else {
            $this->compression = self::$allowedCompressions[0];
        }
    }
        
    /**
     * @param string $id
     * @param mixed $data
     * @param int $lifetime
     * @param string[] $tags
     * @return bool
     */
    public function set($id, $data, $lifetime = null, $tags = array())
    {
        $data = $this->compress($this->serialize($data));
        if (!isset($lifetime)) {
            $lifetime = $this->lifetime;
        }
        if ($this->cacheBackend instanceof Backend\TaggableCache) {
            return $this->cacheBackend->putWithTags($id, $data, $tags, $lifetime);
        } else {
            return $this->cacheBackend->put($id, $data, $lifetime);
        }
    }
        
    /**
     * @param string $id
     * @return mixed
     */
    public function get($id)
    {
        $data = $this->cacheBackend->get($id);
        return $this->unserialize($this->decompress($data));
    }
        
    /**
     * Filter allowed compressions by checking the available functions loaded in php
     */
    protected static function filterAllowedCompressions()
    {
        if (!self::$filteredAllowedCompressions) {
            foreach (self::$allowedCompressions as $compressionIndex => $compressions) {
                switch ($compressions) {
                    case self::COMPRESSION_LZ4:
                        if (!function_exists('lz4_compress')) {
                            unset(self::$allowedCompressions[$compressionIndex]);
                        }
                        break;
                    case self::COMPRESSION_SNAPPY:
                        if (!function_exists('snappy_compress')) {
                            unset(self::$allowedCompressions[$compressionIndex]);
                        }
                        break;
                    case self::COMPRESSION_LZF:
                        if (!function_exists('lzf_compress')) {
                            unset(self::$allowedCompressions[$compressionIndex]);
                        }
                        break;
                    case self::COMPRESSION_GZ:
                        if (!function_exists('gzcompress')) {
                            unset(self::$allowedCompressions[$compressionIndex]);
                        }
                        break;
                }
            }
            self::$allowedCompressions = array_values(self::$allowedCompressions);
            self::$filteredAllowedCompressions = true;
        }
        return self::$allowedCompressions;
    }
        
    /**
     * Serialize the data to be put into cache
     *
     * @param mixed $data
     * @return mixed
     * @throws Exception
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
            case self::SERIALIZATION_NONE:
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
     * @throws Exception
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
            case self::SERIALIZATION_NONE:
            default:
                $unserializedData = $data;
        }
        return $unserializedData;
    }
        
    /**
     * Compress data
     * 
     * @param string $data
     * @return string
     * @throws Exception
     */
    protected function compress($data)
    {
        switch ($this->compression) {
            case self::COMPRESSION_LZ4:
                $compressedData = @lz4_compress($data);
                if (false === $compressedData) {
                    throw new Exception('Error compressing with lz4_compress.');
                }
                break;
            case self::COMPRESSION_SNAPPY:
                $compressedData = @snappy_compress($data);
                if (false === $compressedData) {
                    throw new Exception('Error compressing with snappy_compress.');
                }
                break;
            case self::COMPRESSION_LZF:
                $compressedData = @lzf_compress($data);
                if (false === $compressedData) {
                    throw new Exception('Error compressing with lzf_compress.');
                }
                break;
            case self::COMPRESSION_GZ:
                $compressedData = @gzcompress($data);
                if (false === $compressedData) {
                    throw new Exception('Error compressing with gzcompress.');
                }
                break;
            case self::COMPRESSION_NONE:
            default:
                $compressedData = $data;
        }
        return $compressedData;
    }
        
    /**
     * Decompress data
     *
     * @param string $data
     * @return string
     * @throws Exception
     */
    protected function decompress($data)
    {
        switch ($this->compression) {
            case self::COMPRESSION_LZ4:
                $decompressedData = @lz4_uncompress($data);
                if (false === $decompressedData) {
                    throw new Exception('Error decompressing with lz4_uncompress.');
                }
                break;
            case self::COMPRESSION_SNAPPY:
                $decompressedData = @snappy_uncompress($data);
                if (false === $decompressedData) {
                    throw new Exception('Error decompressing with snappy_uncompress.');
                }
                break;
            case self::COMPRESSION_LZF:
                $decompressedData = @lzf_decompress($data);
                if (false === $decompressedData) {
                    throw new Exception('Error decompressing with lzf_decompress.');
                }
                break;
            case self::COMPRESSION_GZ:
                $decompressedData = @gzuncompress($data);
                if (false === $decompressedData) {
                    throw new Exception('Error decompressing with gzuncompress.');
                }
                break;
            case self::COMPRESSION_NONE:
            default:
                $decompressedData = $data;
        }
        return $decompressedData;
    }
}
