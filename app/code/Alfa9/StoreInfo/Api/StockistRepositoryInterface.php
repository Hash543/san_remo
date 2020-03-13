<?php

namespace Alfa9\StoreInfo\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Alfa9\StoreInfo\Api\Data\StockistInterface;

/**
 * @api
 */

interface StockistRepositoryInterface
{
    /**
     * Save page.
     *
     * @param StockistInterface $stockist
     * @return StockistInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(StockistInterface $stockist);

    /**
     * Retrieve Stockist.
     *
     * @param int $stockistId
     * @return StockistInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($stockistId);

    /**
     * Retrieve pages matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Alfa9\StoreInfo\Api\Data\StockistSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete stockist.
     *
     * @param StockistInterface $stockist
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(StockistInterface $stockist);

    /**
     * Delete stockist by ID.
     *
     * @param int $stockistId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($stockistId);
}
