<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\ConfigurableProduct\Block\ProductAlert\View;
/**
 * Recurring payment view stock
 *
 * @api
 * @since 100.0.2
 */
class Stock extends \Magento\ProductAlert\Block\Product\View\Stock
{
    /**
     * @var \Alfa9\ConfigurableProduct\Helper\ProductAlert\Data
     */
    protected $helperStock;
    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Alfa9\ConfigurableProduct\Helper\ProductAlert\Data $helperStock,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Helper\PostHelper $coreHelper,
        array $data = []
    ){
        $this->helperStock = $helperStock;
        parent::__construct($context, $helperStock, $registry, $coreHelper, $data);
    }

    /**
     * Prepare stock info
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $url = $this->_registry->registry('referer_url');
        if (!$this->_helper->isStockAlertAllowed() || !$this->getProduct() || $this->getProduct()->isAvailable()) {
            $template = '';
        } else {
            $this->setSignupUrl($this->helperStock->getSaveUrlAjax('stock', $url));
        }
        $this->_template = $template;
        return $this;
    }
}
