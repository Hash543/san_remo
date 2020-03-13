<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\ResourceModel\CookieDescription;

use Amasty\GdprCookie\Model\CookieDescription;
use Amasty\GdprCookie\Model\ResourceModel\CookieDescription as CookieDescriptionResource;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;

/**
 * @method CookieDescription[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @var CollectionFactory
     */
    private $factory;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        CollectionFactory $factory,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);

        $this->factory = $factory;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(CookieDescription::class, CookieDescriptionResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function getDescriptionByStore($cookieId, $storeId)
    {
        /** @var Collection $cookieCollection */
        $descriptionCollection = $this->factory->create();

        /** @var CookieDescription $description */
        $description = $descriptionCollection
            ->addFieldToFilter('cookie_id', $cookieId)
            ->addFieldToFilter('store_id', $storeId)
            ->getFirstItem();

        return $description;
    }
}
