<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\ConfigurableProduct\Model;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ConfigurableAttributeData
 * @package Alfa9\ConfigurableProduct\Model
 */
class ConfigurableAttributeData extends \Magento\ConfigurableProduct\Model\ConfigurableAttributeData {


    /**
     * @var array
     */
    protected $attributes = [];
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    protected $entityAttributeCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ConfigurableAttributeData constructor.
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->productRepository = $productRepository;
        $this->entityAttributeCollectionFactory = $entityAttributeCollectionFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Get attribute Code By Id
     * @param $attributeId
     * @return string
     */
    public function getAttributeCodeById($attributeId) {
        if(isset($this->attributes[$attributeId])) {
            return $this->attributes[$attributeId];
        }
        /**
         * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection
         */
        $collection = $this->entityAttributeCollectionFactory->create();
        $collection = $collection->addFieldToFilter('attribute_id', ['eq' => $attributeId])->load();

        /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
        $attribute =  $collection->getFirstItem();
        if($attribute && $attribute->getId()) {
            $this->attributes[$attributeId] = $attribute->getAttributeCode();
        } else {
            $this->attributes[$attributeId] = '';
        }
        return $this->attributes[$attributeId] ;
    }
    /**
     * {@inheritdoc}
     */
    public function getAttributeOptionsData($attribute, $config) {
        $attributeOptionsData = [];
        $attributeCode = $this->getAttributeCodeById($attribute->getAttributeId());
        try {
            $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
            $options = $attribute->getSource()->getAllOptions();
            foreach ($options as $attributeOption) {
                $optionId = $attributeOption['value'];
                if(!isset($config[$attribute->getAttributeId()][$optionId])) {
                    continue;
                }
                $products = isset($config[$attribute->getAttributeId()][$optionId])
                    ? $config[$attribute->getAttributeId()][$optionId]
                    : [];
                $label = $attributeOption['label'];
                if(count($products) == 1 && $attributeCode == 'orden_color') { //If there is only one product we get the description of the product as a Label of the Swatch
                    $productId = current($products);
                    try {
                        /** @var \Magento\Catalog\Model\Product $product */
                        $product = $this->productRepository->getById($productId);
                        $label = $product->getData('short_description');
                        if($label && !empty($label)) {
                            $labelAux = explode(" - ", $label);
                            if(is_array($labelAux) && count($labelAux) == 2) {
                                $label = $labelAux[1];
                            }
                        }
                    }catch (NoSuchEntityException $exception) {
                        $label = $attributeOption['label'];
                    }
                }
                $attributeOptionsData[] = [
                    'id' => $optionId,
                    'label' => $label,
                    'original_label' => $attributeOption['label'],
                    'products' => $products,
                ];
            }
            return $attributeOptionsData;
        } catch (LocalizedException $localizedException) {
            return [];
        }
    }
}