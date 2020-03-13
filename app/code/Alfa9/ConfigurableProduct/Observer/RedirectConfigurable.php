<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\ConfigurableProduct\Observer;

/**
 * Class RedirectConfigurable
 * @package Alfa9\ConfigurableProduct\Observer
 */
class RedirectConfigurable implements \Magento\Framework\Event\ObserverInterface {
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $redirect;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $productResourceTypeConfigurable;
    /**
     * RedirectConfigurable constructor.
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $productResourceTypeConfigurable
     * @param \Magento\Framework\App\Response\Http $redirect
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $productResourceTypeConfigurable,
        \Magento\Framework\App\Response\Http $redirect,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->productResourceTypeConfigurable = $productResourceTypeConfigurable;
        $this->redirect = $redirect;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getEvent()->getData('request');
        if($request && $request->getParam('id')) {
            $simpleProductId = $request->getParam('id');
            try {
                $storeId = $this->storeManager->getStore()->getId();
                $simpleProduct = $this->productRepository->getById($simpleProductId, false, $storeId);
            }catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $storeId = 0;
                $simpleProduct = null;
            }
            if(!$simpleProduct ||
                ($simpleProduct->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
                    && $simpleProduct->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED)) {
                $noRoute = $this->url->getUrl('noroute');
                $this->redirect->setRedirect($noRoute);
                return $this;
            }
            if($simpleProduct->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE /* &&
            $simpleProduct->getVisibility() == \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE */) {
                $configProductIds = $this->productResourceTypeConfigurable->getParentIdsByChild($simpleProductId);
                if(is_array($configProductIds) && count($configProductIds) > 0) {
                    $configProductIds = current($configProductIds);

                    try {
                        $configurableProduct = $this->productRepository->getById($configProductIds, false, $storeId);
                        $configProductUrl = $configurableProduct->getUrlModel()->getUrl($configurableProduct);
                    }catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                        $configProductUrl = '';
                    }
                    if(!empty($configProductUrl)) {
                        $this->redirect->setRedirect($configProductUrl);
                    }
                }
            }
        }
        return $this;
    }
}