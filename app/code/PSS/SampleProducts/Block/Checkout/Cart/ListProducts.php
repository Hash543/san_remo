<?php
/**
 * @author Israel Yasis
 */

namespace PSS\SampleProducts\Block\Checkout\Cart;

/**
 * Class ListProducts
 * @package PSS\SampleProducts\Block\Checkout\Cart
 */
class ListProducts extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \PSS\SampleProducts\Model\Config\Source\ListSampleProducts
     */
    protected $listSampleProducts;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterfaceFactory
     */
    public $_productRepositoryFactory;

    /**
     * ListProducts constructor.
     *
     * @param \PSS\SampleProducts\Model\Config\Source\ListSampleProducts $listSampleProducts
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $data
     */
    public function __construct(
        \PSS\SampleProducts\Model\Config\Source\ListSampleProducts $listSampleProducts,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        $this->formKey = $formKey;
        $this->checkoutSession = $checkoutSession;
        $this->listSampleProducts = $listSampleProducts;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get the List of the products
     * @param array $productIds
     * @return \Magento\Catalog\Model\Product[]|\Magento\Framework\DataObject[]
     */
    public function getList($productIds)
    {
        return $this->listSampleProducts->getList($productIds);
    }

    /**
     * Get the products added in the cart
     * @return array
     */
    public function getProductsInCart()
    {
        $productIds = [];
        $quote = $this->checkoutSession->getQuote();
        if ($quote) {
            /** @var  \Magento\Quote\Model\Quote\Item $item */
            foreach ($quote->getAllItems() as $item) {
                $productIds[] = $item->getProduct()->getId();
            }
        }
        return $productIds;
    }

    /**
     * @return string
     */
    public function getFormKey() {
        return $this->formKey->getFormKey();
    }
}