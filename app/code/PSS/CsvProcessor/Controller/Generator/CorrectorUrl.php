<?php
/**
 * @author Israel Yasis
 */
namespace Pss\CsvProcessor\Controller\Generator;

/**
 * Class CorrectorUrl
 * @package Pss\CsvProcessor\Controller\Generator
 */
class CorrectorUrl extends \Magento\Framework\App\Action\Action {
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    protected $productAction;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;
    /**
     * CorrectorUrl constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Action $productAction
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resource = $resource;
        $this->productAction = $productAction;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute() {
        $productCollection = $this->productCollectionFactory->create();
        $skuUpdate = [];
        $skuFail = [];

        /** get the url empty */
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from([ 'cv'  => 'catalog_product_entity_varchar'], ['entity_id'])
            ->where(new \Zend_Db_Expr("((value = '' OR value is null) and attribute_id = 97)"));

        $dataUrlKey = $connection->fetchAll($select);
        $productIds = [];
        foreach ($dataUrlKey as $row) {
            $productIds[] = (int)$row['entity_id'];
        }
        $productCollection = $productCollection
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('entity_id', ['in' => $productIds])->load();
        $cuantity = count($productCollection);

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($productCollection as $product) {
            $url = $this->sluggify($product->getName());
            try {
                $this->productAction->updateAttributes([$product->getId()], ['url_key' => $url], 0);
                $skuUpdate[] = $product->getSku();
            }catch (\Exception $exception) {
                $skuFail[] = $product->getSku();
            }

        }
        echo "corrected the following skus: ". implode(",", $skuUpdate)."<br>";
        echo "error trying to fix the url with the following skus: ". implode(',', $skuFail).'<br>';
        exit;
    }
    /**
     * @param $string
     * @return mixed|null|string|string[]
     */
    private function sluggify($string) {
        $url = strtolower($string);
        $url = strip_tags($url);
        $url = stripslashes($url);
        $url = html_entity_decode($url);

        $url = str_replace('\'', '', $url);
        $match = '/[^a-z0-9]+/';
        $replace = '-';
        $url = preg_replace($match, $replace, $url);
        $url = trim($url, '-');
        return $url;
    }
}