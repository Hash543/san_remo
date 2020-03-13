<?php
namespace Alfa9\ProductInfo\Block\Ajax;

use Magento\Framework\View\Element\Template;

class Form extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'Alfa9_ProductInfo::catalog/product/view/modal.phtml';

    const XML_PATH_EMAIL_RECIPIENT = 'catalog/email/recipient_email';
    const XML_PATH_EMAIL_SENDER = 'catalog/email/sender_email_identity';
    const XML_PATH_EMAIL_RECOMMEND = 'catalog/email/recomendation_email';
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getFormAction() {
        return $this->getUrl('productinfo/ajax/post');
    }

    /**
     * @return bool|mixed
     */
    public function getProduct() {

        $product  = $this->registry->registry('current_product');
        if($product) return $product;

        return false;
    }

    public function getRecommendationMessage() {
        return $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECOMMEND, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
