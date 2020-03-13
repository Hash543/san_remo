<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Api;

/**
 * @api
 */
interface PageRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Xlanding\Api\Data\PageInterface $page
     * @return \Amasty\Xlanding\Api\Data\PageInterface
     */
    public function save(\Amasty\Xlanding\Api\Data\PageInterface $page);

    /**
     * Get by id
     *
     * @param int $id
     * @return \Amasty\Xlanding\Api\Data\PageInterface
     */
    public function getById($id);

    /**
     * Delete
     *
     * @param \Amasty\Xlanding\Api\Data\PageInterface $page
     * @return bool true on success
     */
    public function delete(\Amasty\Xlanding\Api\Data\PageInterface $page);

    /**
     * Delete by id
     *
     * @param int $id
     * @return bool true on success
     */
    public function deleteById($id);

    /**
     * Lists
     *
     * @param int|null $storeId = null
     * @return \Amasty\Xlanding\Api\Data\PageInterface[] Array of items.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getList($storeId = null);

    /**
     * @param int|null $storeId = null
     * @return \Amasty\Xlanding\Api\Data\PageInterface[] Array of items.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getEnabledList($storeId = null);

    /**
     * @return \Amasty\Xlanding\Model\ResourceModel\Page\Collection
     */
    public function getCollection();
}
