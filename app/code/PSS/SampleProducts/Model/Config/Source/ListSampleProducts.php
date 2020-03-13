<?php
/**
 * @author Israel Yasis
 */
namespace PSS\SampleProducts\Model\Config\Source;

/**
 * Class ListSampleProducts
 * @package PSS\SampleProducts\Model\Config\Source
 */
class ListSampleProducts implements \Magento\Framework\Option\ArrayInterface {

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * ListSampleProducts constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray() {
        $options = [];
        $options[] = [
            'value' => 0, 'label' => __('All Products')
        ];
        foreach ($this->getList() as $item) {
            $options[] = [
                'value' => $item->getId(), 'label' => $item->getName()
            ];
        }
        return $options;
    }
    /**
     * Get the List of the products
     * @param array $productIds
     * @return \Magento\Catalog\Model\Product[]|\Magento\Framework\DataObject[]
     */
    public function getList(array $productIds = []) {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToFilter('sku', [['like' => 'MUE%'], ['like' => 'mue%']])
            ->addAttributeToFilter('is_saleable', ['eq' => 1])
            ->addAttributeToFilter('price', ['eq' => 0])
            ->addAttributeToSelect('short_description')
            ->addAttributeToSelect('name');
        /** if 0 in the array that means show all the products */
        if(count($productIds) > 0 && !in_array(0, $productIds)) {
            $productCollection->addAttributeToFilter('entity_id', ['in' => $productIds]);
        }
        $productCollection->load();
        $items = $productCollection->getItems();
        return $items;
    }
}