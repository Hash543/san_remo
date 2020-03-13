<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\ResourceModel;

use Amasty\GdprCookie\Setup\Operation\CreateCookieDescriptionTable;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CookieDescription extends AbstractDb
{
    public function _construct()
    {
        $this->_init(CreateCookieDescriptionTable::TABLE_NAME, 'id');
    }
}
