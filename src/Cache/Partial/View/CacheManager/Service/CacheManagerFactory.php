<?php

namespace Module\Cache\Partial\View\CacheManager\Service;

use Module\Cache\Partial\View\CacheManager\CacheManager;
use Module\Country\CountryResolver;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CacheManagerFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cacheManager = new CacheManager();
        $cacheManager->setCountry(CountryResolver::getCountry());
        $cacheManager->setLanguageProvider($serviceLocator->get('framework.language-provider'));

        $options = $serviceLocator->get('Cache\Options');
        $cacheManager->setCacheService($serviceLocator->get($options->getCacheServiceKey()));

        return $cacheManager;
    }
}