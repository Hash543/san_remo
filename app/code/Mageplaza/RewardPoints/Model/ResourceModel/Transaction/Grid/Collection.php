<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Model\ResourceModel\Transaction\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Mageplaza\RewardPoints\Model\TransactionFactory;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 * @package Mageplaza\RewardPoints\Model\ResourceModel\Transaction\Grid
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var TransactionFactory $transactionFactory
     */
    protected $transactionFactory;

    /**
     * Collection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param TransactionFactory $transactionFactory
     * @param string $mainTable
     * @param string $resourceModel
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        TransactionFactory $transactionFactory,
        $mainTable = 'mageplaza_reward_transaction',
        $resourceModel = '\Mageplaza\RewardPoints\Model\ResourceModel\Transaction'
    ) {
        $this->transactionFactory = $transactionFactory;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['cus' => $this->getTable('customer_grid_flat')],
            'main_table.customer_id = cus.entity_id',
            ['name', 'email']
        );

        return $this;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $transactionFactory = $this->transactionFactory->create();
        foreach ($items as $item) {
            $title = $transactionFactory->load($item->getTransactionId())->addTitle()->getTitle();
            $item->setData('title', $title);
        }

        return $items;
    }
}
