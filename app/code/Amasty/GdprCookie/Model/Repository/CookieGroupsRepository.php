<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\Repository;

use Amasty\GdprCookie\Api\CookieGroupsRepositoryInterface;
use Amasty\GdprCookie\Api\Data\CookieGroupsInterface;
use Amasty\GdprCookie\Model\CookieGroup;
use Amasty\GdprCookie\Model\CookieGroupFactory;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup as CookieGroupResource;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup\Collection;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup\CollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CookieGroupsRepository implements CookieGroupsRepositoryInterface
{
    /**
     * @var CookieGroupFactory
     */
    private $cookieGroupFactory;

    /**
     * @var CookieGroupResource
     */
    private $cookieGroupResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Model data storage
     *
     * @var array
     */
    private $groups;

    public function __construct(
        CookieGroupFactory $cookieGroupFactory,
        CookieGroupResource $cookieGroupResource,
        CollectionFactory $collectionFactory
    ) {
        $this->cookieGroupFactory = $cookieGroupFactory;
        $this->cookieGroupResource = $cookieGroupResource;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(CookieGroupsInterface $group)
    {
        try {
            if ($group->getId()) {
                $group = $this->getById($group->getId())
                    ->addData($group->getData());
            }
            $this->cookieGroupResource->save($group);
            unset($this->groups[$group->getId()]);
        } catch (\Exception $e) {
            if ($group->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save cookie group with ID %1. Error: %2',
                        [$group->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new cookie group. Error: %1', $e->getMessage()));
        }

        return $group;
    }

    /**
     * @inheritdoc
     */
    public function getById($groupId)
    {
        if (!isset($this->groups[$groupId])) {
            /** @var \Amasty\GdprCookie\Model\CookieGroup $group */
            $group = $this->cookieGroupFactory->create();
            $this->cookieGroupResource->load($group, $groupId);

            if (!$group->getId()) {
                throw new NoSuchEntityException(__('Cookie group with specified ID "%1" not found.', $id));
            }
            $this->groups[$groupId] = $group;
        }

        return $this->groups[$groupId];
    }

    /**
     * @inheritdoc
     */
    public function delete(CookieGroupsInterface $group)
    {
        try {
            $this->cookieGroupResource->delete($group);
            unset($this->groups[$group->getId()]);
        } catch (\Exception $e) {
            if ($group->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove cookie group with ID %1. Error: %2',
                        [$group->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove cookie group. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($cookieId)
    {
        $group = $this->getById($cookieId);

        $this->delete($group);
    }

    /**
     * @inheritdoc
     */
    public function getAllGroups()
    {
        /** @var Collection $groupsCollection */
        $groupsCollection = $this->collectionFactory->create();

        /** @var CookieGroup[] $groups */
        $groups = $groupsCollection->getItems();

        return $groups;
    }
}
