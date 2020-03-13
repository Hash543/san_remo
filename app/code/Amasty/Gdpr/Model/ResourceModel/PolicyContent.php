<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ResourceModel;

use Amasty\Gdpr\Setup\Operation;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PolicyContent extends AbstractDb
{
    public function _construct()
    {
        $this->_init(Operation\CreatePolicyContentTable::TABLE_NAME, 'id');
    }
}
