<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ResourceModel\Policy;

use Amasty\Gdpr\Model\Policy;
use Amasty\Gdpr\Model\ResourceModel\Policy as PolicyResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method Policy[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(Policy::class, PolicyResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function joinContent($storeId)
    {
        $this->getSelect()
            ->joinLeft(
                ['c' => $this->getTable('amasty_gdpr_privacy_policy_content')],
                'main_table.id = c.policy_id AND store_id = ' . intval($storeId),
                ['content' => 'IF(c.id IS NOT NULL, c.content, main_table.content)']
            );

        return $this;
    }
}
