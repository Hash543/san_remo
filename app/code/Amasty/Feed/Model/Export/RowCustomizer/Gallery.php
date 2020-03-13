<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Framework\UrlInterface;

/**
 * Class Gallery
 */
class Gallery implements RowCustomizerInterface
{
    protected $_storeManager;

    protected $_urlPrefix;

    protected $_gallery = [];

    protected $_export;

    protected $productMetadata;

    protected $resource;

    protected $connection;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\ResourceConnection $resource,
        Product $export
    ) {
        $this->_storeManager = $storeManager;
        $this->_export = $export;
        $this->productMetadata = $productMetadata;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
    }

    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        if ($this->_export->hasAttributes(Product::PREFIX_GALLERY_ATTRIBUTE)) {
            $this->_urlPrefix = $this->_storeManager->getStore($collection->getStoreId())
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . 'catalog/product';

            $this->_gallery = $this->_export->getMediaGallery($productIds);
        }
    }

    /**
     * @return array
     */
    public function getGallery()
    {
        return $this->_gallery;
    }

    /**
     * @inheritdoc
     */
    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    /**
     * @inheritdoc
     */
    public function addData($dataRow, $productId)
    {
        $productId = $this->convertEntityIdToRowIdIfNeed($productId);
        $customData = &$dataRow['amasty_custom_data'];
        $gallery = $this->getGallery();
        $gallery = isset($gallery[$productId]) ? $gallery[$productId] : [];
        $storeId = 0;
        if ($gallery) {
            $storeId = $gallery[0]['_media_store_id'];
        }

        $galleryImg = [];

        foreach ($gallery as $key => $data) {
            if ($data['_media_store_id'] == $storeId) {
                $galleryImg[] = $data;
            }
        }

        $customData[Product::PREFIX_GALLERY_ATTRIBUTE] = [
            'image_1' => isset($galleryImg[0]) ? $this->_urlPrefix . $galleryImg[0]['_media_image'] : null,
            'image_2' => isset($galleryImg[1]) ? $this->_urlPrefix . $galleryImg[1]['_media_image'] : null,
            'image_3' => isset($galleryImg[2]) ? $this->_urlPrefix . $galleryImg[2]['_media_image'] : null,
            'image_4' => isset($galleryImg[3]) ? $this->_urlPrefix . $galleryImg[3]['_media_image'] : null,
            'image_5' => isset($galleryImg[4]) ? $this->_urlPrefix . $galleryImg[4]['_media_image'] : null,
        ];

        return $dataRow;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }

    /**
     * @param $ids
     * @return array
     */
    protected function convertEntityIdToRowIdIfNeed($ids)
    {
        if ($this->productMetadata->getEdition() == 'Community') {
            return $ids;
        }

        $tableName = $this->resource->getTableName('catalog_product_entity');
        $select = $this->connection->select()
            ->from($tableName, ['row_id'])
            ->where('entity_id IN (?)', $ids);
        $result = $this->connection->fetchCol($select)[0];

        return $result;
    }
}
