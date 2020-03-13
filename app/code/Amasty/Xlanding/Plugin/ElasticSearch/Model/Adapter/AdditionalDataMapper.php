<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Plugin\ElasticSearch\Model\Adapter;

/**
 * Class AdditionalDataMapper
 * @package Amasty\Xlanding\Plugin\ElasticSearch\Model\Adapter
 */
class AdditionalDataMapper
{
    const FIELD_NAME = 'landing_page_id';
    const INDEX_DOCUMENT = 'document';

    /**
     * @var \Amasty\Xlanding\Model\ResourceModel\Page
     */
    private $pageResource;

    public function __construct(\Amasty\Xlanding\Model\ResourceModel\Page $pageResource)
    {
        $this->pageResource = $pageResource;
    }

    /**
     * Prepare index data for using in search engine metadata.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param $subject
     * @param callable $proceed
     * @param $productId
     * @param array $indexData
     * @param $storeId
     * @param array $context
     * @return array
     */
    public function aroundMap(
        $subject,
        callable $proceed,
        $productId,
        array $indexData,
        $storeId,
        $context = []
    ) {
        $document = $proceed($productId, $indexData, $storeId, $context);

        $pageIdsByProduct = $this->pageResource->getIndexPageIdsByProductId($productId, $storeId);
        if (isset($pageIdsByProduct[$productId]) && !empty($pageIdsByProduct[$productId])) {
            $document[self::FIELD_NAME] = $pageIdsByProduct[$productId];
        }

        return $document;
    }
}
