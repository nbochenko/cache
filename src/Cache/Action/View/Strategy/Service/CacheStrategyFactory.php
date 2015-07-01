<?php

namespace Module\Cache\Action\View\Strategy\Service;

use Module\Cache\Action\View\Strategy\CacheStrategy;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CacheStrategyFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $strategy = new CacheStrategy();
        $cacheConfig = $serviceLocator->get('config')['caches']['app.cache.html'];
        $strategy->setCacheFactoryConfig($cacheConfig);
        $strategy->setDefaultPhpRenderer($serviceLocator->get('ViewRenderer'));
        $strategy->setLanguageProvider($serviceLocator->get('framework.language-provider'));
        $strategy->setConfig($serviceLocator->get('config')['action_cache']);
        $strategy->shit = $serviceLocator->get('framework.sub-view-injector-listener');

        return $strategy;
    }
}