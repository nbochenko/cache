<?php

namespace Module\Cache\Partial\View\Helper\Service;

use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Partial;

class PartialDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        $partialRenderer = $serviceLocator->getServiceLocator()->get('Cache\View\PartialRenderer');
        /** @var Partial $partial */
        $partial = $callback();
        $partial->setView($partialRenderer);

        return $partial;
    }
}