<?php
/**
 * @author Israel Yasis
 */
namespace PSS\ProductAlert\Block\Product;

/**
 * Product view price and stock alerts
 */
class View extends \Magento\ProductAlert\Block\Product\View\Stock {

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\ProductAlert\Helper\Data $helper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\Helper\PostHelper $coreHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\ProductAlert\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Helper\PostHelper $coreHelper,
        array $data = []
    ) {
        parent::__construct($context, $helper, $registry, $coreHelper);
    }

    /**
     * Retrieve currently edited product object
     *
     * @return \Magento\Catalog\Model\Product|boolean
     */
    protected function getProduct()
    {
        $product = $this->_registry->registry('current_product');
        if ($product && $product->getId()) {
            return $product;
        }
        return false;
    }

    /**
     * Retrieve post action config
     *
     * @return string
     */
    public function getPostAction()
    {
        return $this->getUrl('pss_productalert/stock/add');
    }

    /**
     * @return int
     */
    public function getProductId() {
        return $this->getProduct()->getId();
    }
}
