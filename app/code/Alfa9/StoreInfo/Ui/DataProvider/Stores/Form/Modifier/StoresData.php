<?php

namespace Alfa9\StoreInfo\Ui\DataProvider\Stores\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Alfa9\StoreInfo\Model\ResourceModel\Stores\CollectionFactory;

class StoresData implements ModifierInterface
{
    /**
     * @var \Alfa9\StoreInfo\Model\ResourceModel\Stores\Collection
     */
    public $collection;

    /**
     * @param CollectionFactory $stockistCollectionFactory
     */
    public function __construct(
        CollectionFactory $stockistCollectionFactory
    ) {
        $this->collection = $stockistCollectionFactory->create();
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * @param array $data
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyData(array $data)
    {
        $items = $this->collection->getItems();
        /** @var $stockist \Alfa9\StoreInfo\Model\Stores */
        foreach ($items as $stockist) {
            $_data = $stockist->getData();
            if (isset($_data['image'])) {
                $image = [];
                $image[0]['name'] = $stockist->getImage();
                $image[0]['url'] = $stockist->getImageUrl();
                $_data['image'] = $image;
            }
            if (isset($_data['image2'])) {
                $image = [];
                $image[0]['name'] = $stockist->getImage2();
                $image[0]['url'] = $stockist->getImage2Url();
                $_data['image2'] = $image;
            }
            if (isset($_data['image3'])) {
                $image = [];
                $image[0]['name'] = $stockist->getImage3();
                $image[0]['url'] = $stockist->getImage3Url();
                $_data['image3'] = $image;
            }
            if (isset($_data['details_image'])) {
                $image = [];
                $image[0]['name'] = $stockist->getDetailsImage();
                $image[0]['url'] = $stockist->getDetailsImageUrl();
                $_data['details_image'] = $image;
            }
            $stockist->setData($_data);
            $data[$stockist->getId()] = $_data;
        }
        return $data;
    }
}
