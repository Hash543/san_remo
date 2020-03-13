<?php



namespace Firebear\ConfigurableProducts\Api;

use Firebear\ConfigurableProducts\Api\Data\ProductOptionsInterface;

interface ProductOptionsRepositoryInterface
{
    /**
     * @param \Firebear\ConfigurableProducts\Api\Data\ProductOptionsInterface $productOptionsDataInterface
     *
     * @return \Firebear\ConfigurableProducts\Api\Data\ProductOptionsInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(ProductOptionsInterface $productOptionsDataInterface);

    /**
     * @param int $itemId
     *
     * @return \Firebear\ConfigurableProducts\Api\Data\ProductOptionsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($itemId);

    /**
     * @param \Firebear\ConfigurableProducts\Api\Data\ProductOptionsInterface $productOptionsDataInterface
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(ProductOptionsInterface $productOptionsDataInterface);

    /**
     * @param int $itemId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($itemId);
}
