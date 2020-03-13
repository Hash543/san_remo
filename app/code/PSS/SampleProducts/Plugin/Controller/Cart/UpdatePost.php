<?php
/**
 * @author Israel Yasis
 */
namespace PSS\SampleProducts\Plugin\Controller\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class UpdatePost
 * @package PSS\SampleProducts\Plugin\Controller\Cart
 */
class UpdatePost {
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * UpdatePost constructor.
     * @note the cart seems to be deprecated but looks it was deprecated too early
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->messageManager = $messageManager;
        $this->formKey = $formKey;
        $this->productRepository = $productRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->cart = $cart;
    }

    /**
     * @param \Magento\Checkout\Controller\Cart\UpdatePost $subject
     */
    public function beforeExecute(\Magento\Checkout\Controller\Cart\UpdatePost $subject) {
        $request = $subject->getRequest();
        $successSkus = [];
        $errorSkus = [];
        $sampleProducts = $request->getParam('sample_products') ? $request->getParam('sample_products') : [];
        $allSampleProducts = $request->getParam('all_sample_products') ? $request->getParam('all_sample_products') : "" ;
        $allSampleProducts = explode(",", $allSampleProducts);
        $quote = $this->cart->getQuote();
        /**
         * Remove and Add sample Products
         * @var \Magento\Quote\Model\Quote\Item $item
         */
        foreach ($quote->getItemsCollection() as $itemId => $item) {
            $productId = $item->getProduct()->getId();
            $isSampleProduct = \PSS\SampleProducts\Helper\Data::isSampleProductByItem($item);

            if ( $isSampleProduct && $item->getId() === null) {
                $quote->getItemsCollection()->removeItemByKey($itemId);
            } else if($isSampleProduct && !in_array($productId, $sampleProducts) && in_array($productId, $allSampleProducts)){
                $item->isDeleted(true);
            } else if($isSampleProduct && in_array($productId, $sampleProducts)){
                $productId = $item->getProduct()->getId();
                $sampleProducts = array_diff($sampleProducts, [$productId]);
            }
        }
        if ($this->formKeyValidator->validate($request) && $sampleProducts) {
            foreach ($sampleProducts as $productId) {
                $params = [
                    'form_key' => $this->formKey->getFormKey(),
                    'product' => $productId,
                    'qty' => 1
                ];
                /** @var \Magento\Catalog\Model\Product $product */
                try {
                    $product = $this->productRepository->getById($productId);
                }catch (NoSuchEntityException $exception) {
                    $product = null;
                }
                if($product) {
                    try {
                        $this->cart->addProduct($product, $params);
                        $successSkus[] = $product->getSku();
                    }catch (LocalizedException $exception) {
                        $errorSkus[] = $product->getSku();
                    }
                }
            }
        }
        if(count($successSkus) > 0 ) {
            $this->messageManager->addSuccessMessage(
                __("Added successfully the following products: %1", implode($successSkus, ","))
            );
        }
        if(count($errorSkus) > 0 ) {
            $this->messageManager->addErrorMessage(
                __("Error adding the following products: %1", implode($errorSkus, ","))
            );
        }
    }
}