<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Api\Data;

/**
 * Transaction search result interface.
 * @api
 */
interface TransactionSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get items.
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\TransactionInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param \Mageplaza\RewardPointsUltimate\Api\Data\TransactionInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items = null);
}
