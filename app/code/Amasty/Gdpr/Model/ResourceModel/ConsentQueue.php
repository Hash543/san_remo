<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ResourceModel;

use Amasty\Gdpr\Api\Data\ConsentQueueInterface;
use Amasty\Gdpr\Setup\Operation\CreateConsentQueueTable;

class ConsentQueue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _construct()
    {
        $this->_init(CreateConsentQueueTable::TABLE_NAME, ConsentQueueInterface::ID);
    }
}
