<?php

namespace Module\Cache;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Stdlib\ArrayUtils;

class Module
{
    /**
     * @return array|\Traversable|void
     */
    final public function getConfig()
    {
        $config = include __DIR__ . '/config/module.config.php';

        return $config;
    }
}
