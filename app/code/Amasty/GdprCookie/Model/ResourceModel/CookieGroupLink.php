<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\ResourceModel;

use Amasty\GdprCookie\Setup\Operation\CreateCookieGroupLinkTable;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CookieGroupLink extends AbstractDb
{
    public function _construct()
    {
        $this->_init(CreateCookieGroupLinkTable::TABLE_NAME, 'id');
    }
}
