<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\ResourceModel\CookieGroupLink;

use Amasty\GdprCookie\Model\CookieGroupLink;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupLink as CookieGroupResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method CookieGroupLink[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @var CollectionFactory
     */
    private $factory;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
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
        $this->_init(CookieGroupLink::class, CookieGroupResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function getCookiesByGroup($groupId)
    {
        /** @var Collection $cookieCollection */
        $cookieCollection = $this->factory->create();

        /** @var CookieGroupLink[] $cookies */
        $cookies = $cookieCollection->addFieldToFilter('group_id', $groupId)->getItems();

        return $cookies;
    }

    public function getGroupByCookie($cookieId)
    {
        /** @var Collection $groupCollection */
        $groupCollection = $this->factory->create();

        /** @var CookieGroupLink $group */
        $group = $groupCollection->addFieldToFilter('cookie_id', $cookieId)->getFirstItem();

        return $group;
    }
}
