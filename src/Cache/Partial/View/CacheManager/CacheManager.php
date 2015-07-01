<?php

namespace Module\Cache\Partial\View\CacheManager;

use Module\Application\Language\LanguageProvider;
use Zend\Cache\Storage\StorageInterface;

class CacheManager
{
    const KEY_SEPARATOR = '|';

    /**
     * @var StorageInterface
     */
    protected $cacheService;

    /**
     * @var LanguageProvider
     */
    protected $languageProvider;

    /**
     * @var string
     */
    protected $country;

    /**
     * @param      $template
     * @param null $success
     * @param null $casToken
     *
     * @return mixed
     */
    public function getItem($template, & $success = null, & $casToken = null)
    {
        return $this->getCacheService()->getItem($this->generateKey($template), $success, $casToken);
    }

    public function setItem($template, $value)
    {
        $this->getCacheService()->setItem($this->generateKey($template), $value);
    }

    /**
     * @param $template
     *
     * @return string
     */
    protected function generateKey($template)
    {
        return $this->getCountry() . self::KEY_SEPARATOR
            . $this->getLanguageProvider()->getCurrentLanguage() . self::KEY_SEPARATOR
            . $template;
    }

    /**
     * @param \Zend\Cache\Storage\StorageInterface $cacheService
     */
    public function setCacheService(StorageInterface $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * @return \Zend\Cache\Storage\StorageInterface
     */
    public function getCacheService()
    {
        return $this->cacheService;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param \Module\Application\Language\LanguageProvider $languageProvider
     */
    public function setLanguageProvider(LanguageProvider $languageProvider)
    {
        $this->languageProvider = $languageProvider;
    }

    /**
     * @return \Module\Application\Language\LanguageProvider
     */
    public function getLanguageProvider()
    {
        return $this->languageProvider;
    }
}