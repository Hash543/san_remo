<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ConfigurableProducts\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Firebear\ConfigurableProducts\Model\UrlGenerator;
use Magento\Catalog\Model\Product\Visibility;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Command prints list of available currencies
 */
class GenerateUrlRewrite extends Command
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * Generates url rewrites for different scopes.
     *
     * @var ProductScopeRewriteGenerator
     */
    private $rewriteGenerator;

    /**
     * GenerateUrlRewrite constructor.
     *
     * @param CollectionFactory            $collectionFactory
     * @param StoreManagerInterface        $storeManager
     * @param UrlPersistInterface          $urlPersist
     * @param ProductScopeRewriteGenerator $rewriteGenerator
     *
     * @internal param CollectionFactory $сollectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        UrlPersistInterface $urlPersist,
        UrlGenerator $rewriteGenerator
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->urlPersist = $urlPersist;
        $this->rewriteGenerator = $rewriteGenerator;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('firebear:url-rewrite:generate')
            ->setDescription('Generate Url Rewrites for hidden products');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $stores = $this->storeManager->getStores();

        $productCollection = $this->collectionFactory->create();
        $productCollection
            ->addFieldToFilter('type_id', ['eq' => 'simple'])
            ->addFieldToFilter(
                'visibility',
                ['eq' => Visibility::VISIBILITY_NOT_VISIBLE]
            )
            ->addAttributeToSelect(['name', 'url_path', 'url_key', 'visibility']);

        $productsCount = $productCollection->getSize();
        $output->writeln('<info>Found ' . $productsCount . ' products</info>');
        $output->write("\033[0G");

        $rowNumber = 1;
        foreach ($productCollection as $product) {
            $rowNumber++;
            $progress = floor($rowNumber / 100);
            $output->write("\033[0G");
            $output->write("Progress: $progress% ($rowNumber/$productsCount)");

            foreach ($stores as $store) {
                $filterData = [
                    UrlRewrite::ENTITY_ID   => $product->getId(),
                    UrlRewrite::ENTITY_TYPE => UrlGenerator::ENTITY_TYPE,
                    UrlRewrite::STORE_ID    => $store->getId(),
                ];

                $rewrite = $this->urlPersist->findOneByData($filterData);

                if (!$rewrite) {
                    $product->setStoreId($store->getId());
                    $this->urlPersist->replace($this->generateUrls($product));
                }
            }
        }
        $output->write("\033[0G");
        $output->writeln("Progress: 100% ($productsCount/$productsCount)");
        $output->writeln('<info>Done</info>');
    }

    /**
     * Generate product urls.
     *
     * @param Product $product
     *
     * @return array|UrlRewrite[]
     */
    private function generateUrls(Product $product)
    {
        $storeId = $product->getStoreId();

        $productCategories = $product->getCategoryCollection()
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url_path');

        $urls = $this->rewriteGenerator->isGlobalScope($storeId)
            ? $this->rewriteGenerator->generateForGlobalScope($productCategories, $product)
            : $this->rewriteGenerator->generateForSpecificStoreView($storeId, $productCategories, $product);

        return $urls;
    }
}
