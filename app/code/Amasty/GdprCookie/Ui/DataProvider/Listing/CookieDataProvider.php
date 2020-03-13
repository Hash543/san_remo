<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Ui\DataProvider\Listing;

class CookieDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Amasty\GdprCookie\Model\ResourceModel\Cookie\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Amasty\GdprCookie\Model\ResourceModel\Cookie\CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collectionFactory = $collectionFactory;
    }

    public function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create()->addGroupsColumn();
        }

        return $this->collection;
    }

    public function addOrder($field, $direction)
    {
        if ($field === "group") {
            $field = "COALESCE(groups.name, \"None\")";
        }
        parent::addOrder($field, $direction);
    }


    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        switch ($filter->getField()) {
            case 'id':
                $field = 'main_table.id';
                break;
            case 'name':
                $field = 'main_table.name';
                break;
            case 'group':
                $field = 'groups.id';
                break;
        }
        if ($filter->getValue() === "0" && $filter->getField() === "group") {
            $this->getCollection()->getSelect()->where('ISNULL(group_id)');
        } else {
            $this->getCollection()->addFieldToFilter(
                $field,
                [$filter->getConditionType() => $filter->getValue()]
            );
        }
    }
}
