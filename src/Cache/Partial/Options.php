<?php

namespace Module\Cache\Partial;

class Options
{
    /**
     * @var string
     */
    protected $cacheServiceKey;

    /**
     * @var []
     */
    protected $config;

    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (isset($options['cache_service_key'])) {
            $this->setCacheServiceKey($options['cache_service_key']);
        }
        if (isset($options['config'])) {
            $this->setConfig($options['config']);
        }
    }

    /**
     * @param string $cacheServiceKey
     */
    public function setCacheServiceKey($cacheServiceKey)
    {
        $this->cacheServiceKey = $cacheServiceKey;
    }

    /**
     * @return string
     */
    public function getCacheServiceKey()
    {
        return $this->cacheServiceKey;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }
}