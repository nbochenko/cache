<?php

namespace Module\Cache\Partial\View;

use Module\Cache\Partial\Options;
use Module\Cache\Partial\View\CacheManager\CacheManager;
use Module\Cache\Partial\View\Renderer\PartialRenderer;
use Zend\Cache\Storage\StorageInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\View\Model\ViewModel;
use Zend\View\ViewEvent;

class PartialStrategy implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * @var callable
     */
    protected $partialRendererFactoryCallback;

    /**
     * @var PartialRenderer
     */
    protected $renderer;

    /**
     * @var Options
     */
    protected $options;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @inheritdoc
     */
    public function attach(EventManagerInterface $events)
    {
        $events->attach(ViewEvent::EVENT_RENDERER, [$this, 'onRenderer'], 100);
        $events->attach(ViewEvent::EVENT_RESPONSE, [$this, 'onResponse'], 100);
        $events->attach(ViewEvent::EVENT_RESPONSE, [$this, 'onResponse'], 100);
    }

    /**
     * @param ViewEvent $viewEvent
     *
     * @return PartialRenderer
     */
    public function onRenderer(ViewEvent $viewEvent)
    {
        $model = $viewEvent->getModel();
        $config = $this->getOptions()->getConfig();
        if (
            $model instanceof ViewModel
            && in_array($model->getTemplate(), $config)
        ) {
            $success = false;
            $cacheContent = $this->getCacheManager()->getItem($model->getTemplate(), $success);
            if (true === $success) {
                $partialModel = new PartialViewModel();
                $partialModel->setIsCached(true);
                $partialModel->setCacheContent($cacheContent);
                $partialModel->setTemplate($model->getTemplate());
                $viewEvent->setModel($model);
            }

            return $this->getRenderer();
        }
    }

    /**
     * @param ViewEvent $e
     */
    public function onResponse(ViewEvent $e)
    {
        $renderer = $e->getRenderer();
        if ($renderer !== $this->renderer) {
            return;
        }

        $result   = $e->getResult();
        $response = $e->getResponse();

        $response->setContent($result);
    }

    /**
     * @param \Module\Cache\Partial\View\Renderer\PartialRenderer $renderer
     */
    public function setRenderer(PartialRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @return \Module\Cache\Partial\View\Renderer\PartialRenderer
     */
    public function getRenderer()
    {
        if (null === $this->renderer) {
            $this->renderer = call_user_func($this->getPartialRendererFactoryCallback());
        }
        return $this->renderer;
    }

    /**
     * @param callable $partialRendererFactoryCallback
     */
    public function setPartialRendererFactoryCallback(callable $partialRendererFactoryCallback)
    {
        $this->partialRendererFactoryCallback = $partialRendererFactoryCallback;
    }

    /**
     * @return callable
     */
    public function getPartialRendererFactoryCallback()
    {
        return $this->partialRendererFactoryCallback;
    }

    /**
     * @param \Module\Cache\Partial\Options $options
     */
    public function setOptions(Options $options)
    {
        $this->options = $options;
    }

    /**
     * @return \Module\Cache\Partial\Options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param \Module\Cache\Partial\View\CacheManager\CacheManager $cacheManager
     */
    public function setCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @return \Module\Cache\Partial\View\CacheManager\CacheManager
     */
    public function getCacheManager()
    {
        return $this->cacheManager;
    }
}