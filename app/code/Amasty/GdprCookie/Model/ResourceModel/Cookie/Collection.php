<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\ResourceModel\Cookie;

use Amasty\GdprCookie\Model\Cookie;
use Amasty\GdprCookie\Model\ResourceModel\Cookie as CookieResource;
use Amasty\GdprCookie\Setup\Operation\CreateCookieGroupLinkTable;
use Amasty\GdprCookie\Setup\Operation\CreateCookieGroupsTable;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method Cookie[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(Cookie::class, CookieResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function addGroupsColumn()
    {
        $linkTable = $this->getTable(CreateCookieGroupLinkTable::TABLE_NAME);
        $groupTable = $this->getTable(CreateCookieGroupsTable::TABLE_NAME);

        $this->getSelect()->joinLeft(
            ['link' => $linkTable],
            'main_table.id = link.cookie_id',
            'link.group_id'
        )->joinLeft(
            ['groups' => $groupTable],
            'link.group_id = groups.id',
            ['group' => 'COALESCE(groups.name, "None")']
        );

        return $this;
    }
}
