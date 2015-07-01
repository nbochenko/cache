<?php

namespace Module\Cache\Partial\View\Renderer\Service;

use Module\Cache\Partial\Options;
use Module\Cache\Partial\View\Renderer\PartialRenderer;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PartialRendererFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $renderer = new PartialRenderer();
        $renderer->setPhpRenderer($serviceLocator->get('Zend\View\Renderer\PhpRenderer'));
        $renderer->setOptions($serviceLocator->get('Cache\Options'));
        $renderer->setCacheManager($serviceLocator->get('Cache\CacheManager'));

        return $renderer;
    }
}