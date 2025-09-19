<?php

/**
 * Caching System Class
 * 
 * Handles data caching with multiple drivers
 */
class Cache
{
    private static $instance = null;
    private $driver;
    private $config;
    
    private function __construct()
    {
        $this->config = require_once __DIR__ . '/../../config/cache.php';
        $this->initializeDriver();
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize cache driver
     */
    private function initializeDriver()
    {
        $defaultDriver = $this->config['default'];
        $driverConfig = $this->config['stores'][$defaultDriver];
        
        switch ($defaultDriver) {
            case 'file':
                $this->driver = new FileCacheDriver($driverConfig);
                break;
            case 'redis':
                $this->driver = new RedisCacheDriver($driverConfig);
                break;
            case 'memcached':
                $this->driver = new MemcachedCacheDriver($driverConfig);
                break;
            case 'array':
                $this->driver = new ArrayCacheDriver($driverConfig);
                break;
            default:
                $this->driver = new FileCacheDriver($driverConfig);
        }
    }
    
    /**
     * Get cached value
     */
    public function get($key, $default = null)
    {
        try {
            $value = $this->driver->get($this->prefixKey($key));
            return $value !== null ? $value : $default;
        } catch (Exception $e) {
            Logger::error('Cache get failed: ' . $e->getMessage());
            return $default;
        }
    }
    
    /**
     * Set cached value
     */
    public function set($key, $value, $ttl = null)
    {
        try {
            $ttl = $ttl ?? $this->config['ttl']['default'];
            return $this->driver->set($this->prefixKey($key), $value, $ttl);
        } catch (Exception $e) {
            Logger::error('Cache set failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete cached value
     */
    public function delete($key)
    {
        try {
            return $this->driver->delete($this->prefixKey($key));
        } catch (Exception $e) {
            Logger::error('Cache delete failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear all cache
     */
    public function clear()
    {
        try {
            return $this->driver->clear();
        } catch (Exception $e) {
            Logger::error('Cache clear failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if key exists
     */
    public function has($key)
    {
        return $this->get($key) !== null;
    }
    
    /**
     * Get multiple values
     */
    public function getMultiple($keys, $default = null)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }
    
    /**
     * Set multiple values
     */
    public function setMultiple($values, $ttl = null)
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * Delete multiple values
     */
    public function deleteMultiple($keys)
    {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * Remember value (get or set)
     */
    public function remember($key, $callback, $ttl = null)
    {
        $value = $this->get($key);
        
        if ($value === null) {
            $value = $callback();
            $this->set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    /**
     * Increment value
     */
    public function increment($key, $value = 1)
    {
        try {
            return $this->driver->increment($this->prefixKey($key), $value);
        } catch (Exception $e) {
            Logger::error('Cache increment failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Decrement value
     */
    public function decrement($key, $value = 1)
    {
        try {
            return $this->driver->decrement($this->prefixKey($key), $value);
        } catch (Exception $e) {
            Logger::error('Cache decrement failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add prefix to key
     */
    private function prefixKey($key)
    {
        return $this->config['prefix'] . ':' . $key;
    }
    
    /**
     * Get cache statistics
     */
    public function getStats()
    {
        try {
            return $this->driver->getStats();
        } catch (Exception $e) {
            Logger::error('Cache stats failed: ' . $e->getMessage());
            return [];
        }
    }
}

/**
 * File Cache Driver
 */
class FileCacheDriver
{
    private $path;
    
    public function __construct($config)
    {
        $this->path = $config['path'];
        
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }
    
    public function get($key)
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($file));
        
        if ($data['expires'] && $data['expires'] < time()) {
            $this->delete($key);
            return null;
        }
        
        return $data['value'];
    }
    
    public function set($key, $value, $ttl)
    {
        $file = $this->getFilePath($key);
        $expires = $ttl ? time() + $ttl : null;
        
        $data = [
            'value' => $value,
            'expires' => $expires
        ];
        
        return file_put_contents($file, serialize($data)) !== false;
    }
    
    public function delete($key)
    {
        $file = $this->getFilePath($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }
    
    public function clear()
    {
        $files = glob($this->path . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        return true;
    }
    
    public function increment($key, $value)
    {
        $current = $this->get($key) ?: 0;
        $new = $current + $value;
        $this->set($key, $new, null);
        return $new;
    }
    
    public function decrement($key, $value)
    {
        $current = $this->get($key) ?: 0;
        $new = $current - $value;
        $this->set($key, $new, null);
        return $new;
    }
    
    public function getStats()
    {
        $files = glob($this->path . '/*');
        return [
            'files' => count($files),
            'size' => array_sum(array_map('filesize', $files))
        ];
    }
    
    private function getFilePath($key)
    {
        return $this->path . '/' . md5($key) . '.cache';
    }
}

/**
 * Array Cache Driver (for testing)
 */
class ArrayCacheDriver
{
    private $data = [];
    
    public function __construct($config) {}
    
    public function get($key)
    {
        if (!isset($this->data[$key])) {
            return null;
        }
        
        $item = $this->data[$key];
        
        if ($item['expires'] && $item['expires'] < time()) {
            unset($this->data[$key]);
            return null;
        }
        
        return $item['value'];
    }
    
    public function set($key, $value, $ttl)
    {
        $expires = $ttl ? time() + $ttl : null;
        
        $this->data[$key] = [
            'value' => $value,
            'expires' => $expires
        ];
        
        return true;
    }
    
    public function delete($key)
    {
        unset($this->data[$key]);
        return true;
    }
    
    public function clear()
    {
        $this->data = [];
        return true;
    }
    
    public function increment($key, $value)
    {
        $current = $this->get($key) ?: 0;
        $new = $current + $value;
        $this->set($key, $new, null);
        return $new;
    }
    
    public function decrement($key, $value)
    {
        $current = $this->get($key) ?: 0;
        $new = $current - $value;
        $this->set($key, $new, null);
        return $new;
    }
    
    public function getStats()
    {
        return [
            'keys' => count($this->data),
            'memory' => memory_get_usage()
        ];
    }
}