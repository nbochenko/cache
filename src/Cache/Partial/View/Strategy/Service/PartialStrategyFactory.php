<?php

namespace Module\Cache\Partial\View\Service;

use Module\Cache\Partial\View\PartialStrategy;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PartialStrategyFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $partialStrategy = new PartialStrategy();
        $options = $serviceLocator->get('Cache\Options');
        $partialStrategy->setOptions($options);
        $partialStrategy->setCacheManager($serviceLocator->get('Cache\CacheManager'));

        $partialStrategy->setPartialRendererFactoryCallback(
            function () use ($serviceLocator) {
                return $serviceLocator->get('Cache\View\PartialRenderer');
            }
        );

        return $partialStrategy;
    }
}