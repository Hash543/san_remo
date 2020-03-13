<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ResourceModel;

use Amasty\Gdpr\Api\Data\WithConsentInterface;
use Amasty\Gdpr\Setup\Operation\CreateConsentLogTable;

class WithConsent extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _construct()
    {
        $this->_init(CreateConsentLogTable::TABLE_NAME, WithConsentInterface::ID);
    }

    /**
     * @param $customerId
     *
     * @return array
     */
    public function getConsentsByCustomerId($customerId)
    {
        $table = $this->getTable(CreateConsentLogTable::TABLE_NAME);
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($table)
            ->where('customer_id = ?', $customerId);

        return $connection->fetchAll($select);
    }
}
