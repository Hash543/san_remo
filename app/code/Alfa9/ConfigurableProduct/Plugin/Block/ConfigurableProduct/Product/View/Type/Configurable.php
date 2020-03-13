<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\ConfigurableProduct\Plugin\Block\ConfigurableProduct\Product\View\Type;

use Alfa9\ConfigurableProduct\Setup\InstallData as AttributeInstall;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as Subject;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Configurable
 * @package Alfa9\ConfigurableProduct\Plugin\Block\ConfigurableProduct\Product\View\Type
 */
class Configurable {
    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @var \Magento\Framework\Json\Encoder
     */
    protected $encoder;
    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $localeFormat;
    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;
    /**
     * @var \Magento\Catalog\Helper\Image $imageHelper
     */
    protected $imageHelper;
    /**
     * @var \Alfa9\ConfigurableProduct\Helper\Data
     */
    protected $helperConfigurable;

    /**
     * @var \Alfa9\ConfigurableProduct\Model\ConfigurableAttributeData
     */
    protected $configurableAttributeData;
    /**
     * Configurable constructor.
     * @param \Alfa9\ConfigurableProduct\Model\ConfigurableAttributeData $configurableAttributeData
     * @param \Alfa9\ConfigurableProduct\Helper\Data $helperConfigurable
     * @param StockConfigurationInterface $stockConfiguration
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\Framework\Json\Encoder $encoder
     * @param \Magento\Framework\Locale\Format $localeFormat
     */
    public function __construct(
        \Alfa9\ConfigurableProduct\Model\ConfigurableAttributeData $configurableAttributeData,
        \Alfa9\ConfigurableProduct\Helper\Data $helperConfigurable,
        StockConfigurationInterface $stockConfiguration,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Framework\Json\Encoder $encoder,
        \Magento\Framework\Locale\Format $localeFormat
    ) {
        $this->configurableAttributeData = $configurableAttributeData;
        $this->helperConfigurable = $helperConfigurable;
        $this->stockConfiguration = $stockConfiguration;
        $this->imageHelper = $imageHelper;
        $this->jsonDecoder = $jsonDecoder;
        $this->encoder = $encoder;
        $this->localeFormat = $localeFormat;
    }
    /**
     * Get All used products for configurable
     *
     * @param Subject $subject
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeGetAllowProducts(
        Subject $subject
    ) {
        if (!$subject->hasAllowProducts() &&
            $this->stockConfiguration->isShowOutOfStock()) {
            /** @var Product $product */
            $product = $subject->getProduct();
            $allowProducts = [];
            $usedProducts = $product->getTypeInstance()
                ->getUsedProducts($product);
            /** @var Product $usedProduct */
            foreach ($usedProducts as $usedProduct) {
                if ($usedProduct->getStatus() == Status::STATUS_ENABLED) {
                    $allowProducts[] = $usedProduct;
                }
            }
            $subject->setData('allow_products', $allowProducts);
        }
        return $subject->getData('allow_products');
    }

    /**
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param array result
     *
     * @return string
     */
    public function afterGetJsonConfig(
        Subject $subject,
        $result
    ) {
        $config = $this->jsonDecoder->decode($result);
        $currentProduct = $subject->getProduct();
        $options = $this->helperConfigurable->getOptions($currentProduct, $subject->getAllowProducts());
        $product = $subject->getProduct();
        //Enable disable the out of stock children here
        $attributeData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);
        if(isset($attributeData['attributes'])) {
            $config['attributes'] = $attributeData['attributes'];
        }
        $this->applyOrder($config, $product);
        $swatchImages = [];
        $saleableProducts = [];
        $images = [];
        $stockExpress = [];
        foreach ($subject->getAllowProducts() as $product) {
            $productId = $product->getId();
            /** Add the Pvd Prices */
            if(isset($config['optionPrices'])) {
                $this->getPvpPrice($config, $product);
            }
            $images[$productId] = $this->getImages($product, $swatchImages);
            $saleableProducts[$productId] = $product->isSaleable();
            $stockExpress[$productId] = $product->getData('express_stock') > 0  ? true : false;
        }
        $config['saleableProducts'] = $saleableProducts;
        $config['swatches'] = $swatchImages;
        $config['images'] = $images;
        $config['stockExpress'] = $stockExpress;
        return $this->encoder->encode($config);
    }

    /**
     * Apply Order
     * @param $config
     * @param Product $product
     */
    private function applyOrder(&$config, Product $product){
        $orderSwatch = $product->getData(AttributeInstall::ATTRIBUTE_ORDER_SWATCH);
        /** Sort By attribute Swatch */
        if($orderSwatch) {
            $attributeOrders = explode(',', $orderSwatch);
            if(count($attributeOrders) > 0 && isset($config['attributes'])){
                foreach ($config['attributes'] as $key => $attribute) {
                    if(!isset($attribute['options'])) {
                        continue;
                    }
                    foreach ($attribute['options'] as $keyOption => $option) {
                        $config['attributes'][$key]['options'][$keyOption]['order'] = $this->findOrderBySwatch($attributeOrders, $option);
                    }
                    $sortedOptions = $config['attributes'][$key]['options'];
                    $config['attributes'][$key]['options'] = [];
                    usort($sortedOptions, function($a, $b) {
                        return $a['order'] <=> $b['order'];
                    });
                    $config['attributes'][$key]['options'] = $sortedOptions;
                }
            }
        } else {
            //Todo: Sort by Price
            foreach ($config['attributes'] as $key => $attribute) {
                foreach ($attribute['options'] as $keyOption => $option) {
                    $config['attributes'][$key]['options'][$keyOption]['price'] = $this->findOrderByLowestPrice($config, $option);
                }
                $sortedOptions = $config['attributes'][$key]['options'];
                $config['attributes'][$key]['options'] = [];
                usort($sortedOptions, function($a, $b) {
                    return ($a['price'] <=> $b['price']);
                });
                $config['attributes'][$key]['options'] = $sortedOptions;
            }
        }
    }
    /**
     * Add Pvp Price to Config
     * @param $config
     * @param Product $product
     */
    public function getPvpPrice(&$config, Product $product) {
        $productId = $product->getId();
        $priceInfo = $product->getPriceInfo();
        $finalPrice = $priceInfo->getPrice('final_price')->getAmount()->getValue();
        $pvpPrice = $product->getData('pvp');
        if($pvpPrice && $pvpPrice > 0) {
            $pvpPrice = round($pvpPrice, 2);
            $savePercent = (1 - ($finalPrice / $pvpPrice)) * 100;
            $discountPvp = round($savePercent, 0);
            $config['optionPrices'][$productId]['pvpPrice'] = [
                'amount' => $this->localeFormat->getNumber($pvpPrice)
            ];
            $config['optionPrices'][$productId]['oldPrice'] = [
                'amount' => $this->localeFormat->getNumber($pvpPrice)
            ];
            $config['optionPrices'][$productId]['pvpDiscount'] = [
                'amount' => $discountPvp. '%'
            ];
        }
    }
    /**
     * Filter the images
     * @param array $swatchImages
     * @param Product $product
     * @return array
     */
    public function getImages(Product $product, &$swatchImages) {
        $productId = $product->getId();

        /** Add images swatches */
        $productImages = $this->helperConfigurable->getGalleryImages($product) ?: [];
        $entries = $product->getMediaGalleryEntries();
        $images = [];
        /** @var \Magento\Catalog\Model\Product\Image $image */
        foreach ($productImages as $image) {
            if(\Alfa9\ConfigurableProduct\Helper\Data::isSwatch($product, $image->getFile())) {
                $swatchImages[$productId] = [
                    'url' => $this->imageHelper->init($product, 'swatch_configurable')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                ];
            }

            $imageHasType = true;
            /** @var \Magento\Catalog\Model\Product\Gallery\Entry $entry */
            foreach ($entries as $entry) {
                $types = $entry->getTypes();

                if($image->getId() == $entry->getId()
                    && in_array("colores", $types)) {
                    $imageHasType = false;
                    break;
                }
            }

            if($imageHasType) {
                $images[] =
                    [
                        'thumb' => $image->getData('small_image_url'),
                        'img' => $image->getData('medium_image_url'),
                        'full' => $image->getData('large_image_url'),
                        'caption' => $image->getLabel(),
                        'position' => $image->getPosition(),
                        'isMain' => $image->getFile() == $product->getImage(),
                        'type' => str_replace('external-', '', $image->getMediaType()),
                        'videoUrl' => $image->getVideoUrl(),
                    ];
            }
        }

        return $images;
    }

    /**
     * @param array $config
     * @return array
     */
    public function filterSwatchImages ($config) {
        $newImages = [];
        if(isset($config['images'])) {
            $newImages = [];
            foreach ($config['images'] as $productId => $productImages) {
                $newImages[$productId] = [];
                foreach ($productImages as $image) {
                    if(isset($image['full']) && \Alfa9\ConfigurableProduct\Helper\Data::isSwatch($image['full'])) {
                        continue;
                    }
                    $newImages[$productId][] = $image;
                }
            }
        }
        return $newImages;
    }

    /**
     * @param $config
     * @param $option
     * @return int
     */
    private function findOrderByLowestPrice($config, $option) {
        if(isset($config['optionPrices']) && isset($option['products'])) {
            $lowestPrice = PHP_INT_MAX;
            foreach ($option['products'] as $key => $productId) {
                if(isset($config['optionPrices'][$productId]) && isset($config['optionPrices'][$productId]['finalPrice'])
                && isset($config['optionPrices'][$productId]['finalPrice']['amount'])) {
                    $finalPrice = $config['optionPrices'][$productId]['finalPrice']['amount'];
                    if($lowestPrice > $finalPrice) {
                        $lowestPrice = $finalPrice;
                    }
                }
            }
            return $lowestPrice;
        }
        return 0;
    }
    /**
     * Seek the order in the attribute of the product
     * @param array $swatchOrders
     * @param array $option
     * @return int
     */
    private function findOrderBySwatch(array $swatchOrders, array $option) {
        foreach ($swatchOrders as $swatchOption) {
            if(!isset($option['original_label'])) {
                continue;
            }
            $swatchOrder = explode('=', $swatchOption);
            if(count($swatchOrder) == 2 && $swatchOrder[0] == $option['original_label']) {
                return (int)$swatchOrder[1];
            }
        }
        return 0;
    }
}