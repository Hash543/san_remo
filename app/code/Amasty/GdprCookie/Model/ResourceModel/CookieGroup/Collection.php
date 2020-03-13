<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\ResourceModel\CookieGroup;

use Amasty\GdprCookie\Model\CookieGroup;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup as CookieGroupResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method CookieGroup[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(CookieGroup::class, CookieGroupResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
