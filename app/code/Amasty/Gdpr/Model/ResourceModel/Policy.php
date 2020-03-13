<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ResourceModel;

use Amasty\Gdpr\Api\Data\WithConsentInterface;
use Amasty\Gdpr\Setup\Operation;
use Amasty\Gdpr\Setup\Operation\CreateConsentLogTable;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Amasty\Gdpr\Api\Data\PolicyInterface;

class Policy extends AbstractDb
{
    public function _construct()
    {
        $this->_init(Operation\CreatePolicyTable::TABLE_NAME, 'id');
    }

    /**
     * @param $except
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function disableAllPolicies($except)
    {
        $this->getConnection()->update(
            $this->getMainTable(),
            ['status' => \Amasty\Gdpr\Model\Policy::STATUS_DISABLED],
            [
                'id != ?' => $except,
                'status != ?' => \Amasty\Gdpr\Model\Policy::STATUS_DRAFT
            ]
        );
    }

    /**
     * Get column values with policy id
     *
     * @param $column
     * @return array
     */
    public function getAllValueFromColumnPolicy($column)
    {
        $select = $this->getConnection()->select()
            ->from(['policy' => $this->getTable(Operation\CreatePolicyTable::TABLE_NAME)])
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns(['id', $column]);
        return $this->getConnection()->fetchAll($select);
    }

    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->changeConsentVersionAfterDeletePolicy($object->getPolicyVersion());

        return $this;
    }

    /**
     * @param string $policyVersion
     * @return $this
     */
    private function changeConsentVersionAfterDeletePolicy($policyVersion)
    {
        $connection = $this->getConnection();

        $connection->update(
            $this->getTable(CreateConsentLogTable::TABLE_NAME),
            [WithConsentInterface::POLICY_VERSION => $policyVersion . '_deleted'],
            [WithConsentInterface::POLICY_VERSION . ' = ?' => $policyVersion]
        );

        return $this;
    }
}
