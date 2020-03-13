<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\ResourceModel;

use Amasty\GdprCookie\Setup\Operation\CreateCookieConsentTable;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CookieConsent extends AbstractDb
{
    public function _construct()
    {
        $this->_init(CreateCookieConsentTable::TABLE_NAME, 'id');
    }
}
