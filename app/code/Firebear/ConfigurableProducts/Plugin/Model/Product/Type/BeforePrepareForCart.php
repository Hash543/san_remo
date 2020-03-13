<?php

namespace Firebear\ConfigurableProducts\Plugin\Model\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;

class BeforePrepareForCart
{
    private $logger;
    private $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository, LoggerInterface $logger)
    {
        $this->productRepository = $productRepository;
        $this->logger            = $logger;
    }

    public function beforePrepareForCartAdvanced(
        $product,
        \Magento\Framework\DataObject $buyRequest,
        $selection,
        $processMode = null
    ) {
        $this->unsetEmptyItems($buyRequest);

        // Restore the attributes on each iteration
        if ($selection->getTypeId() == 'configurable') {
            if (isset($buyRequest['only_for_checkbox_bundle'][$selection->getOptionId()][$selection->getProductId()])) {
                $attributes = $buyRequest->getSuperAttribute();
                if ($buyRequest->getOsa() !== null) {
                    $attributes = $buyRequest->getOsa();
                }
                if (isset($attributes[$selection->getOptionId()][$selection->getProductId()])) {
                    $buyRequest->setOsa($attributes);
                    $buyRequest->setSuperAttribute($attributes[$selection->getOptionId()][$selection->getProductId()]);
                }
            } else {
                if ($buyRequest->getOsa() !== null) {
                    $buyRequest->setSuperAttribute($buyRequest->getOsa());
                }

                $attributes = $buyRequest->getSuperAttribute();

                if (isset($attributes[$selection->getOptionId()])) {
                    $buyRequest->setOsa($buyRequest->getSuperAttribute());
                    $buyRequest->setSuperAttribute($attributes[$selection->getOptionId()]);
                }
            }
        }

        $fullProduct = $this->productRepository->getById($selection->getId());
        $selection->setOptions($fullProduct->getOptions());

        $bundleOptions = $buyRequest->getBundleCustomOptions();
        if (is_array($bundleOptions) && isset($bundleOptions[$selection->getOptionId()])) {
            $optionData = $bundleOptions[$selection->getOptionId()];
            $buyRequest->addData(['options' => $optionData]);
        }

        return [$buyRequest, $selection, $processMode];
    }

    /**
     * Unset all empty items
     *
     * @param \Magento\Framework\DataObject $buyRequest
     */
    protected function unsetEmptyItems(\Magento\Framework\DataObject $buyRequest)
    {
        $qtys            = $buyRequest->getBundleOptionQty();
        $optionData      = $buyRequest->getBundleOption();
        $superAttributes = $buyRequest->getSuperAttribute();

        if (is_array($qtys)) {
            foreach ($qtys as $id => $value) {
                if ($value == 0) {
                    unset($qtys[$id]);
                    unset($optionData[$id]);
                    unset($superAttributes[$id]);
                }
            }
        }

        $buyRequest->setBundleOptionQty($qtys);
        $buyRequest->setBundleOption($optionData);
        $buyRequest->setSuperAttribute($superAttributes);
    }
}