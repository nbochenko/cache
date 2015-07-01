<?php

return [
    'partial_cache'   => [
        'options' => [
            'cache_service_key' => 'Cache\Service\Name',
            'config'            => [
            ]
        ],
    ],
    'action_cache'    => [
//        'Property\Controller\Search:homepage' => [
//            'count' => 10,
//            'ttl'   => 300
//        ],
    ],
    'service_manager' => [
        'factories' => [
            'Cache\View\PartialStrategy' => 'Module\Cache\Partial\View\Service\PartialStrategyFactory',
            'Cache\Options'              => 'Module\Cache\Partial\Service\OptionsFactory',
            'Cache\View\PartialRenderer' => 'Module\Cache\Partial\View\Renderer\Service\PartialRendererFactory',
            'Cache\CacheManager'         => 'Module\Cache\Partial\View\CacheManager\Service\CacheManagerFactory',
            'Cache\View\ActionStrategy'  => 'Module\Cache\Action\View\Strategy\Service\CacheStrategyFactory',
        ]
    ],
    'view_manager'    => [
        'strategies' => [
            'Cache\View\PartialStrategy',
            'Cache\View\ActionStrategy'
        ]
    ],
    'view_helpers'    => [
        'delegators' => [
            'partial' => [
                'Module\Cache\Partial\View\Helper\Service\PartialDelegatorFactory'
            ]
        ]
    ]
];
