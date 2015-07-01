<?php
/**
 * Created by PhpStorm.
 * User: nbochenko
 * Date: 2/3/15
 * Time: 3:58 PM
 */

namespace Module\Cache\Action\View\Strategy;

use Module\Country\CountryResolver;
use Module\Framework\Language\LanguageProviderInterface;
use Module\Cache\Action\View\Model\CacheModel;
use Zend\Cache\Storage\Adapter\AbstractAdapter;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\ResolverInterface;
use Zend\View\ViewEvent;

class CacheStrategy implements ListenerAggregateInterface, RendererInterface
{
    use ListenerAggregateTrait;

    /**
     * String separator
     */
    const KEY_SEPARATOR = ':';

    /**
     * Event custom parameter
     */
    const EVENT_PARAM_KEY = __CLASS__;

    /**
     * @var []
     */
    protected $cacheFactoryConfig;

    /**
     * @var AbstractAdapter
     */
    protected $cacheServices = [];

    /**
     * @var PhpRenderer
     */
    protected $defaultPhpRenderer;

    /**
     * @var LanguageProviderInterface
     */
    protected $languageProvider;

    /**
     * @var []
     */
    protected $config;

    /**
     * @inheritdoc
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEventManager = $events->getSharedManager();
        $sharedEventManager->attach('Zend\Mvc\Controller\AbstractActionController', MvcEvent::EVENT_DISPATCH, [$this, 'onDispatchPre'], 10);
        $sharedEventManager->attach('Zend\Mvc\Controller\AbstractActionController', MvcEvent::EVENT_DISPATCH, [$this, 'onDispatchPost'], -99);
        $events->attach(ViewEvent::EVENT_RENDERER, [$this, 'onSelectRenderer'], 2);
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, [$this, 'restoreRenderer'], 10);
    }

    /**
     * @param string|ModelInterface $nameOrModel
     * @param null                  $values
     * @return string|void
     */
    public function render($nameOrModel, $values = null)
    {
        if ($nameOrModel instanceof CacheModel) {
            $key = $nameOrModel->getCacheKey();
            if (false === $nameOrModel->getIsFetchable()) {
                $cacheService = $this->getCacheService($key);
                $result = $this->getDefaultPhpRenderer()->render($nameOrModel, $values = null);
                $cacheService->setItem($key, $result);

                return $result;
            } else {
                return $nameOrModel->getContent();
            }
        }

        return $this->getDefaultPhpRenderer()->render($nameOrModel, $values = null);
    }

    /**
     * If the action is meant to be cached, stops the propagation, and inject the CacheModel to his parent.
     *
     * @param MvcEvent $e
     * @return mixed
     */
    public function onDispatchPre(MvcEvent $e)
    {
        $key = $this->getKey($e->getRouteMatch());

        if (!array_key_exists($key, $this->config)) {
            return $e;
        }

        $cacheKeyConfig = $this->config[$key];
        $key .= self::KEY_SEPARATOR . CountryResolver::getCountry()
            . self::KEY_SEPARATOR . $this->getLanguageProvider()->getCurrentLanguage();
        if (isset($cacheKeyConfig['count'])) {
            $key .= self::KEY_SEPARATOR . mt_rand(1, $cacheKeyConfig['count']);
        }
        if (isset($cacheKeyConfig['route_params']) && true === $cacheKeyConfig['route_params']) {
            $routeParams = $e->getRouteMatch()->getParams();
            $key .= self::KEY_SEPARATOR . md5(serialize($routeParams));
        }
        $cache = $this->getCacheService($key, $cacheKeyConfig['ttl']);

        // Custom param set to be caught by the onDispatchPost method
        $e->setParam(self::EVENT_PARAM_KEY, $key);

        // If our cache requirements are met
        if (null !== ($html = $cache->getItem($key))) {
            $result = new CacheModel();
            $result->setContent($html);
            $result->setIsFetchable(true);
            $result->setCacheKey($key);
            $e->setResult($result);
            $e->stopPropagation(true);

            $model = $e->getViewModel();
            $model->addChild($result);

            return $e->getResult();
        }
    }

    public $shit;

    /**
     * We take the ViewModel and replace it to a CacheModel in case it has to be cached at the rendering
     *
     * @param MvcEvent $e
     * @return MvcEvent
     */
    public function onDispatchPost(MvcEvent $e)
    {
        $key = $this->getKey($e->getRouteMatch());

        if (!array_key_exists($key, $this->config)) {
            return $e;
        }

        $viewModel = $e->getResult();
        $result = new CacheModel($viewModel->getVariables());
        $result->setTemplate($viewModel->getTemplate());
        $result->setTerminal($viewModel->terminate());
        $result->setCacheKey($e->getParam(self::EVENT_PARAM_KEY));
        $result->setIsFetchable(false);
        $e->setResult($result);

        return $e;
    }

    /**
     * @param RouteMatch $routeMatch
     * @return string
     */
    public static function getKey(RouteMatch $routeMatch)
    {
        return $routeMatch->getParam('controller') . self::KEY_SEPARATOR
        . $routeMatch->getParam('action');
    }

    /**
     * Restore the default PhpRenderer, in order to let Zend\View\Strategy\PhpRendererStrategy
     * to trigger his injectResponse()
     *
     * @param ViewEvent $e
     */
    public function restoreRenderer(ViewEvent $e)
    {
        $renderer = $e->getRenderer();
        if ($renderer === $this) {
            $e->setRenderer($this->getDefaultPhpRenderer());
        }
    }

    /**
     * Automatically returns the renderer, cause this event is triggered on a low priority,
     * just before the basic Php one.
     *
     * @param ViewEvent $e
     *
     * @return $this
     */
    public function onSelectRenderer(ViewEvent $e)
    {
        return $this;
    }

    /**
     * @param      $key
     * @param null $ttl
     * @return StorageInterface
     */
    public function getCacheService($key, $ttl = null)
    {
        if (!isset ($this->cacheServices[$key]) || null === $this->cacheServices[$key]) {
            $cacheConfig = $this->getCacheFactoryConfig();
            $cacheConfig['options']['namespace'] .= $key;
            null === $ttl ? : $cacheConfig['options']['ttl'] = $ttl;
            $this->cacheServices[$key] = StorageFactory::factory($cacheConfig);
        }

        return $this->cacheServices[$key];
    }

    /**
     * @param mixed $cacheFactoryConfig
     */
    public function setCacheFactoryConfig($cacheFactoryConfig)
    {
        $this->cacheFactoryConfig = $cacheFactoryConfig;
    }

    /**
     * @return mixed
     */
    public function getCacheFactoryConfig()
    {
        return $this->cacheFactoryConfig;
    }

    /**
     * @inheritdoc
     */
    public function getEngine()
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setResolver(ResolverInterface $resolver)
    {
        return $this;
    }

    /**
     * @param \Zend\View\Renderer\PhpRenderer $defaultPhpRenderer
     */
    public function setDefaultPhpRenderer($defaultPhpRenderer)
    {
        $this->defaultPhpRenderer = $defaultPhpRenderer;
    }

    /**
     * @return \Zend\View\Renderer\PhpRenderer
     */
    public function getDefaultPhpRenderer()
    {
        return $this->defaultPhpRenderer;
    }

    /**
     * @param LanguageProviderInterface $languageProvider
     */
    public function setLanguageProvider(LanguageProviderInterface $languageProvider)
    {
        $this->languageProvider = $languageProvider;
    }

    /**
     * @return LanguageProviderInterface
     */
    public function getLanguageProvider()
    {
        return $this->languageProvider;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }
}