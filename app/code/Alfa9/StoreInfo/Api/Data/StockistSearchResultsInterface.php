<?php

namespace Alfa9\StoreInfo\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface StockistSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get stockist list.
     *
     * @return \Alfa9\StoreInfo\Api\Data\StockistInterface[]
     */
    public function getItems();

    /**
     * Set stockists list.
     *
     * @param \Alfa9\StoreInfo\Api\Data\StockistInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
