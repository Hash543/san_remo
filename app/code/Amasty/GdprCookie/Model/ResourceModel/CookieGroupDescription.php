<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\ResourceModel;

use Amasty\GdprCookie\Setup\Operation\CreateCookieGroupDescriptionTable;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CookieGroupDescription extends AbstractDb
{
    public function _construct()
    {
        $this->_init(CreateCookieGroupDescriptionTable::TABLE_NAME, 'id');
    }
}
