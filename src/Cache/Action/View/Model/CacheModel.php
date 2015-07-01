<?php

namespace Module\Cache\Action\View\Model;

use Zend\View\Model\ViewModel;

class CacheModel extends ViewModel
{
    /**
     * Whether the cache is fetchable, or storable
     * true for fetchable
     * false for storable
     *
     * @var bool
     */
    protected $isFetchable;

    /**
     * The HTML cached content fetched from the cache
     *
     * @var string
     */
    protected $content;

    /**
     * The cache key to store into the cache
     *
     * @var string
     */
    protected $cacheKey;


    /**
     * @param mixed $isFetchable
     */
    public function setIsFetchable($isFetchable)
    {
        $this->isFetchable = $isFetchable;
    }

    /**
     * @return mixed
     */
    public function getIsFetchable()
    {
        return $this->isFetchable;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $cacheKey
     */
    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * @return mixed
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }
}