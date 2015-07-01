<?php
/**
 * Created by PhpStorm.
 * User: nbochenko
 * Date: 6/22/15
 * Time: 5:07 PM
 */

namespace Module\Cache\Partial\View;


use Zend\View\Model\ViewModel;

class PartialViewModel extends ViewModel
{
    /**
     * @var bool
     */
    protected $isCached;

    /**
     * @var string
     */
    protected $cacheContent;

    /**
     * @param boolean $isCached
     */
    public function setIsCached($isCached)
    {
        $this->isCached = $isCached;
    }

    /**
     * @return boolean
     */
    public function getIsCached()
    {
        return $this->isCached;
    }

    /**
     * @param string $cacheContent
     */
    public function setCacheContent($cacheContent)
    {
        $this->cacheContent = $cacheContent;
    }

    /**
     * @return string
     */
    public function getCacheContent()
    {
        return $this->cacheContent;
    }

    public function setChildren()
    {

    }
}