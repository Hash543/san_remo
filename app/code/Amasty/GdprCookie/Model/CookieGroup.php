<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model;

use Amasty\GdprCookie\Api\Data\CookieGroupsInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class CookieGroup extends AbstractModel implements CookieGroupsInterface, IdentityInterface
{
    const CACHE_TAG = 'amasty_cookie_groups';

    public function _construct()
    {
        $this->_init(ResourceModel\CookieGroup::class);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData(CookieGroupsInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(CookieGroupsInterface::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->_getData(CookieGroupsInterface::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->setData(CookieGroupsInterface::DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function getIsEssential()
    {
        return $this->_getData(CookieGroupsInterface::IS_ESSENTIAL);
    }

    /**
     * @inheritdoc
     */
    public function setIsEssential($isEssential)
    {
        $this->setData(CookieGroupsInterface::IS_ESSENTIAL, $isEssential);
    }

    /**
     * @inheritdoc
     */
    public function getIsEnabled()
    {
        return $this->_getData(CookieGroupsInterface::IS_ENABLED);
    }

    /**
     * @inheritdoc
     */
    public function setIsEnabled($isEnabled)
    {
        $this->setData(CookieGroupsInterface::IS_ENABLED, $isEnabled);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }

    /**
     * Get list of cache tags applied to model object.
     *
     * @return array
     */
    public function getCacheTags()
    {
        $tags = parent::getCacheTags();
        if (!$tags) {
            $tags = [];
        }
        return $tags + $this->getIdentities();
    }
}
