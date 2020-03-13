<?php

namespace Firebear\ConfigurableProducts\Plugin\Model\ConfigurableProduct\Product\Type;

use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Firebear\ConfigurableProducts\Helper\Data as ICPHelper;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\RequestInterface;

class Configurable extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    private $icpHelper;
    private $logger;
    private $request;
    private $productRepository;
    public function __construct(
        ICPHelper $icpHelper,
        LoggerInterface $logger,
        RequestInterface $request
    ) {
        $this->icpHelper = $icpHelper;
        $this->logger = $logger;
        $this->request = $request;
    }

    public function aroundAddFilterByRequiredOptions(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $subject,
        callable $proceed
    ) {
        $fullActionName= $this->request->getFullActionName();
        if ($this->icpHelper->getGeneralConfig('matrix/matrix_swatch')) {
            if ($fullActionName == 'catalog_product_view'
                || $fullActionName == 'checkout_cart_add'
                || $fullActionName == 'customer_section_load'
                || $fullActionName == 'checkout_cart_index'
                || $fullActionName == 'checkout_index_index'
                || $fullActionName == '__'
                || $fullActionName == 'catalog_category_view') {
                return $subject;
            } else {
                return $proceed();
            }
        } else {
            return $subject;
        }
    }
}
