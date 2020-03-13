<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model;

use Magento\Framework\Model\AbstractModel;

class CookieDescription extends AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceModel\CookieDescription::class);
    }
}
