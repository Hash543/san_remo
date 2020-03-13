<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Api;

/**
 * @api
 */
interface CookieGroupsRepositoryInterface
{
    /**
     * Save Cookie Group
     *
     * @param \Amasty\GdprCookie\Api\Data\CookieGroupsInterface $group
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieGroupsInterface
     */
    public function save(\Amasty\GdprCookie\Api\Data\CookieGroupsInterface $group);

    /**
     * Get cookie group by id
     *
     * @param int $groupId
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieGroupsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($groupId);

    /**
     * Delete Cookie Group
     *
     * @param \Amasty\GdprCookie\Api\Data\CookieGroupsInterface $group
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GdprCookie\Api\Data\CookieGroupsInterface $group);

    /**
     * Delete cookie group by id
     *
     * @param int $groupId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($groupId);

    /**
     * Get all cookie groups
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieGroupsInterface[]
     */
    public function getAllGroups();
}
