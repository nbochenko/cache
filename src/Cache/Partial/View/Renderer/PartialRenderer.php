<?php

namespace Module\Cache\Partial\View\Renderer;

use Module\Application\Language\LanguageProvider;
use Module\Cache\Partial\Options;
use Module\Cache\Partial\View\CacheManager\CacheManager;
use Module\Cache\Partial\View\PartialViewModel;
use Module\Country\CountryResolver;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\ResolverInterface;

class PartialRenderer implements RendererInterface
{
    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var PhpRenderer
     */
    protected $phpRenderer;

    /**
     * @var Options
     */
    protected $options;

    /**
     * @var LanguageProvider
     */
    protected $languageProvider;

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
     * Will check in the Cache Service if there is something for this partial, then retrieve it,
     * otherwise, will give the PHPRenderer the responsability to execute the view.
     *
     * @param string|\Zend\View\Model\ModelInterface $nameOrModel
     * @param null                                   $values
     *
     * @return mixed|string
     */
    public function render($nameOrModel, $values = null)
    {
        $config = $this->getOptions()->getConfig();
        if (is_string($nameOrModel)) {
            $template = $nameOrModel;
        } elseif ($nameOrModel instanceof PartialViewModel) {
            if (true === $nameOrModel->getIsCached()) {
                return $nameOrModel->getCacheContent();
            }
        } elseif ($nameOrModel instanceof ViewModel) {
            $template = $nameOrModel->getTemplate();
        }
        if (!in_array($template, $config)) {
            return $this->getPhpRenderer()->render($nameOrModel, $values);
        }

        $success = false;
        $html = $this->getCacheManager()->getItem($template, $success);
        if ($success !== true) {
            $html = $this->getPhpRenderer()->render($nameOrModel, $values);
            $html = \Minify_HTML::minify($html);
            $this->getCacheManager()->setItem($template, $html);
        }

        return $html;
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

    /**
     * @param \Zend\View\Renderer\PhpRenderer $phpRenderer
     */
    public function setPhpRenderer(PhpRenderer $phpRenderer)
    {
        $this->phpRenderer = $phpRenderer;
    }

    /**
     * @return \Zend\View\Renderer\PhpRenderer
     */
    public function getPhpRenderer()
    {
        return $this->phpRenderer;
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
     * @param \Module\Application\Language\LanguageProvider $languageProvider
     */
    public function setLanguageProvider(LanguageProvider $languageProvider)
    {
        $this->languageProvider = $languageProvider;
    }

    /**
     * @return \Module\Application\Language\LanguageProvider
     */
    public function getLanguageProvider()
    {
        return $this->languageProvider;
    }
}