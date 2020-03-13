<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\ConfigurableProduct\Helper;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Helper\Data as ParentHelperData;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Data
 * Helper class for getting options
 * @api
 * @since 100.0.2
 */
class Data extends ParentHelperData {

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * Data constructor.
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Image $imageHelper
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->productRepository = $productRepository;
        parent::__construct($imageHelper);
    }


    /**
     * @param $product
     * @param $file
     *
     * @return bool
     */
    public static function isSwatch($product, $file) {
        $colorImage = strtolower($product->getColores());
        $filename = strtolower($file);

        if ($colorImage === $filename) {
            return true;
        }

        return false;
    }
}
