<?php

namespace Firebear\ConfigurableProducts\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\EntityManager\MetadataPool;

class LinkManagement extends \Magento\Bundle\Model\LinkManagement
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addChild(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        $optionId,
        \Magento\Bundle\Api\Data\LinkInterface $linkedProduct
    ) {
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new InputException(
                __('Product with specified sku: "%1" is not a bundle product', $product->getSku())
            );
        }

        $options = $this->optionCollection->create();
        $options->setIdFilter($optionId);
        $existingOption = $options->getFirstItem();

        if (!$existingOption->getId()) {
            throw new InputException(
                __(
                    'Product with specified sku: "%1" does not contain option: "%2"',
                    [$product->getSku(), $optionId]
                )
            );
        }

        $linkField        = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $resource         = $this->bundleFactory->create();
        $selections       = $resource->getSelectionsData($product->getData($linkField));
        $linkProductModel = $this->productRepository->get($linkedProduct->getSku());

        if ($selections) {
            foreach ($selections as $selection) {
                if ($selection['option_id'] == $optionId
                    && $selection['product_id'] == $linkProductModel->getEntityId()) {
                    if (!$product->getCopyFromView()) {
                        throw new CouldNotSaveException(
                            __(
                                'Child with specified sku: "%1" already assigned to product: "%2"',
                                [$linkedProduct->getSku(), $product->getSku()]
                            )
                        );
                    } else {
                        return $this->bundleSelection->create()->load($linkProductModel->getEntityId());
                    }
                }
            }
        }

        $selectionModel = $this->bundleSelection->create();
        $selectionModel = $this->mapProductLinkToSelectionModel(
            $selectionModel,
            $linkedProduct,
            $linkProductModel->getEntityId(),
            $product->getData($linkField)
        );
        $selectionModel->setOptionId($optionId);

        try {
            $selectionModel->save();
            $resource->addProductRelation($product->getData($linkField), $linkProductModel->getEntityId());
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save child: "%1"', $e->getMessage()), $e);
        }

        return $selectionModel->getId();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function saveChild(
        $sku,
        \Magento\Bundle\Api\Data\LinkInterface $linkedProduct
    ) {
        $product = $this->productRepository->get($sku, true);
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            throw new InputException(
                __('Product with specified sku: "%1" is not a bundle product', [$product->getSku()])
            );
        }

        /** @var \Magento\Catalog\Model\Product $linkProductModel */
        $linkProductModel = $this->productRepository->get($linkedProduct->getSku());

        if (!$linkedProduct->getId()) {
            throw new InputException(__('Id field of product link is required'));
        }

        /** @var \Magento\Bundle\Model\Selection $selectionModel */
        $selectionModel = $this->bundleSelection->create();
        $selectionModel->load($linkedProduct->getId());
        if (!$selectionModel->getId()) {
            throw new InputException(__('Can not find product link with id "%1"', [$linkedProduct->getId()]));
        }
        $linkField      = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $selectionModel = $this->mapProductLinkToSelectionModel(
            $selectionModel,
            $linkedProduct,
            $linkProductModel->getId(),
            $product->getData($linkField)
        );

        try {
            $selectionModel->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save child: "%1"', $e->getMessage()), $e);
        }

        return true;
    }

    /**
     * Get MetadataPool instance
     *
     * @return MetadataPool
     */
    private function getMetadataPool()
    {
        if (!$this->metadataPool) {
            $this->metadataPool = ObjectManager::getInstance()->get(MetadataPool::class);
        }

        return $this->metadataPool;
    }
}
