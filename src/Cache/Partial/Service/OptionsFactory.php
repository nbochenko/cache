<?php

namespace Module\Cache\Partial\Service;

use Module\Cache\Partial\Options;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OptionsFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $options = new Options($serviceLocator->get('config')['partial_cache']['options']);

        return $options;
    }
}