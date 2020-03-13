<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model;

use Amasty\GdprCookie\Api\Data\CookieInterface;
use Magento\Framework\Model\AbstractModel;

class Cookie extends AbstractModel implements CookieInterface
{
    public function _construct()
    {
        $this->_init(ResourceModel\Cookie::class);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData(CookieInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(CookieInterface::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->_getData(CookieInterface::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->setData(CookieInterface::DESCRIPTION, $description);
    }

}
