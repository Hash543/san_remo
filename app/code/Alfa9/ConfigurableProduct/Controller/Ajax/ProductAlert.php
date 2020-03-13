<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\ConfigurableProduct\Controller\Ajax;

use Magento\Framework\Exception\NoSuchEntityException;
/**
 * Class ProductAlert
 * @package Alfa9\ConfigurableProduct\Controller\Ajax
 */
class ProductAlert extends \Magento\Framework\App\Action\Action {

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * ProductAlert constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute() {
        $productId = $this->getRequest()->getParam('productId');
        try {
            $product = $this->productRepository->getById($productId);
            $this->registry->register('current_product', $product);
            $this->registry->register('product', $product);
            $this->registry->register('referer_url', $this->_redirect->getRefererUrl());
        }catch (NoSuchEntityException $exception) {
            return '';
        }
        return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_LAYOUT);
    }
}