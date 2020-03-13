<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\ResourceModel\PolicyContent;

use Amasty\Gdpr\Model\PolicyContent;
use Amasty\Gdpr\Model\ResourceModel\PolicyContent as PolicyContentResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method PolicyContent[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @var CollectionFactory
     */
    private $factory;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger, \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        CollectionFactory $factory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->factory = $factory;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(PolicyContent::class, PolicyContentResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param $policyId
     * @param $storeId
     *
     * @return PolicyContent
     */
    public function findByStoreAndPolicy($policyId, $storeId)
    {
        /** @var Collection $contentCollection */
        $contentCollection = $this->factory->create();

        /** @var PolicyContent $content */
        $content = $contentCollection
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('policy_id', $policyId)
            ->getFirstItem();

        return $content;
    }
}
