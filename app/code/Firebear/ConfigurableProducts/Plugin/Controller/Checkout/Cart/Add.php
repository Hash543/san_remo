<?php
/**
 * Copyright © 2017 Firebear Studio. All rights reserved.
 */

namespace Firebear\ConfigurableProducts\Plugin\Controller\Checkout\Cart;

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Catalog\Api\ProductRepositoryInterface;


class Add extends \Magento\Checkout\Controller\Cart\Add
{

    private $cartFactory;
    private $optionRepository;
    private $optionModel;

    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 101.1.0
     */
    protected $serializer;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Catalog\Model\Product\Option $optionModel,
        \Magento\Catalog\Model\Product\Option\Repository $optionRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->cartFactory      = $cartFactory;
        $this->optionRepository = $optionRepository;
        $this->optionModel      = $optionModel;
        $this->serializer       = $serializer
            ?: \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository
        );
    }

    public function aroundExecute(\Magento\Checkout\Controller\Cart\Add $subject, callable $proceed)
    {
        $product = null;
        if (!$subject->_formKeyValidator->validate($subject->getRequest())) {
            return $subject->resultRedirectFactory->create()->setPath('*/*/');
        }
        $params = $this->getRequest()->getParams();

        $matrixSwatch = $this->_scopeConfig->getValue(
            'firebear_configurableproducts/matrix/matrix_swatch',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $product = $this->productRepository->getById($this->getRequest()->getParam('product'));
        if ($product->getTypeId() == 'bundle') {
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
            
            $params = $this->getRequest()->getParams();
            try {
                if (isset($params['qty'])) {
                    $filter        = new \Zend_Filter_LocalizedToNormalized(
                        [
                            'locale' => $this->_objectManager->get(
                                \Magento\Framework\Locale\ResolverInterface::class
                            )->getLocale()
                        ]
                    );
                    $params['qty'] = $filter->filter($params['qty']);
                }

                $product = $this->_initProduct();
                $related = $this->getRequest()->getParam('related_product');

                /**
                 * Check product availability
                 */
                if (!$product) {
                    return $this->goBack();
                }
                $this->cart->addProduct($product, $params);
                if (!empty($related)) {
                    $this->cart->addProductsByIds(explode(',', $related));
                }

                $this->cart->save();

                /**
                 * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
                 */
                $this->_eventManager->dispatch(
                    'checkout_cart_add_product_complete',
                    ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                );

                if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                    if (!$this->cart->getQuote()->getHasError()) {
                        $message = __(
                            'You added %1 to your shopping cart.',
                            $product->getName()
                        );
                        $this->messageManager->addSuccessMessage($message);
                    }

                    return $this->goBack(null, $product);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->_checkoutSession->getUseNotice(true)) {
                    $this->messageManager->addNotice(
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
                    );
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $this->messageManager->addError(
                            $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($message)
                        );
                    }
                }

                $url = $this->_checkoutSession->getRedirectUrl(true);

                if (!$url) {
                    $cartUrl = $this->_objectManager->get(\Magento\Checkout\Helper\Cart::class)->getCartUrl();
                    $url     = $this->_redirect->getRedirectUrl($cartUrl);
                }

                return $this->goBack($url);
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);

                return $this->goBack();
            }
        }
        if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual'
            || $product->getTypeId() == 'downloadable'
            || $product->getTypeId() == 'grouped'
            || (!isset($params['qty_matrix_product'])
                && !isset($params['options'])
                && !$matrixSwatch)) {
            parent::execute();
            return $this->goBack(null);

        } elseif ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual'
            || $product->getTypeId() == 'downloadable'
            || $product->getTypeId() == 'grouped'
            || (!isset($params['qty_matrix_product'])
                && !isset($params['options'])
                && $matrixSwatch)) {
            parent::execute();
            return $this->goBack(null);

        } elseif (isset($params['options']) && !isset($params['qty_matrix_product']) && !$matrixSwatch) {
            try {
                if (isset($params['qty'])) {
                    $filter        = new \Zend_Filter_LocalizedToNormalized(
                        [
                            'locale' => $this->_objectManager->get(
                                \Magento\Framework\Locale\ResolverInterface::class
                            )->getLocale()
                        ]
                    );
                    $params['qty'] = $filter->filter($params['qty']);
                }

                $product = $this->_initProduct();
                $related = $this->getRequest()->getParam('related_product');

                /**
                 * Check product availability
                 */
                if (!$product) {
                    return $this->goBack();
                }
                if (isset($params['options'])) {
                    $this->addCustomizibleOpionsToProduct($params, $product);
                }
                $this->cart->addProduct($product, $params);
                if (!empty($related)) {
                    $this->cart->addProductsByIds(explode(',', $related));
                }

                $this->cart->save();

                /**
                 * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
                 */
                $this->_eventManager->dispatch(
                    'checkout_cart_add_product_complete',
                    ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                );

                if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                    if (!$this->cart->getQuote()->getHasError()) {
                        $message = __(
                            'You added %1 to your shopping cart.',
                            $product->getName()
                        );
                        $this->messageManager->addSuccessMessage($message);
                    }

                    return $this->goBack(null, $product);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->_checkoutSession->getUseNotice(true)) {
                    $this->messageManager->addNotice(
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
                    );
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $this->messageManager->addError(
                            $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($message)
                        );
                    }
                }

                $url = $this->_checkoutSession->getRedirectUrl(true);

                if (!$url) {
                    $cartUrl = $this->_objectManager->get(\Magento\Checkout\Helper\Cart::class)->getCartUrl();
                    $url     = $this->_redirect->getRedirectUrl($cartUrl);
                }

                return $this->goBack($url);
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);

                return $this->goBack();
            }
        } elseif ($product->getTypeId() != 'bundle') {
            try {
                $cartModel = $this->cartFactory->create();
                $errorFlag = true;
                foreach ($params['qty_matrix_product'] as $optionId => $matrixProductValue) {
                    foreach ($matrixProductValue as $valueId => $qtyProduct) {
                        if ($qtyProduct <= 0) {
                            continue;
                        } else {
                            $errorFlag = false;
                        }


                        $filter = new \Zend_Filter_LocalizedToNormalized(
                            [
                                'locale' => $subject->_objectManager->get('Magento\Framework\Locale\ResolverInterface')
                                    ->getLocale()
                            ]
                        );

                        $params['qty']                        = $filter->filter($qtyProduct);
                        $params['super_attribute'][$optionId] = '' . $valueId . '';
                        $storeId                              = $this->_objectManager->get(
                            'Magento\Store\Model\StoreManagerInterface'
                        )->getStore()->getId();
                        $product                              = $this->_objectManager->create(
                            'Magento\Catalog\Model\Product'
                        )->setStoreId($storeId)->load($params['product']);
                        $idSuperAttributesArray               = [];
                        foreach ($params['super_attribute'] as $key => $value) {
                            $idSuperAttributesArray[] = $key;
                        }
                        arsort($params['super_attribute']);

                        $this->getRequest()->setParams($params);

                        /**
                         * Check product availability
                         */
                        if (!$product) {
                            return $subject->goBack();
                        }
                        if (isset($params['options'])) {
                            $this->addCustomizibleOpionsToProduct($params, $product);
                        }
                        $this->cart->addProduct($product, $params);

                        /**
                         * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
                         */
                        $subject->_eventManager->dispatch(
                            'checkout_cart_add_product_complete',
                            [
                                'product'  => $product,
                                'request'  => $subject->getRequest(),
                                'response' => $subject->getResponse()
                            ]
                        );

                        if (!$subject->_checkoutSession->getNoCartRedirect(true)) {
                            if (!$subject->cart->getQuote()->getHasError()) {
                                $message = __(
                                    'You added %1 to your shopping cart.',
                                    $product->getName()
                                );
                                $subject->messageManager->addSuccessMessage($message);
                            }
                        }
                    }
                }
                $cartModel->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($subject->_checkoutSession->getUseNotice(true)) {
                    $subject->messageManager->addNotice(
                        $subject->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage())
                    );
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $subject->messageManager->addError(
                            $subject->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($message)
                        );
                    }
                }

                $url = $subject->_checkoutSession->getRedirectUrl(true);

                if (!$url) {
                    $cartUrl = $subject->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl();
                    $url     = $subject->_redirect->getRedirectUrl($cartUrl);
                }

                return $subject->goBack(false);

            } catch (\Exception $e) {
                $subject->messageManager->addException(
                    $e,
                    __('We can\'t add this item to your shopping cart right now.') . $e->getMessage()
                );
                $subject->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);

                return $subject->goBack();
            }
            if (!$product || $errorFlag) {
                $subject->messageManager->addWarning(
                    __('Qty not specified for any product.')
                );

                return $subject->goBack();
            }

            return $subject->goBack(null, $product);
        }
    }

    protected function addCustomizibleOpionsToProduct($params, &$product)
    {
        $additionalOptions = [];
        foreach ($params['options'] as $optionId => $option) {
            if (empty($option)) {
                continue;
            }
            $optionModel   = $this->optionModel->load($optionId);
            $productId     = $optionModel->getProductId();
            $productOption = $this->productRepository->getById($productId);
            $optionModel   = $this->optionRepository->get(
                $productOption->getSku(),
                $optionId
            );
            $optionValue   = null;
            foreach ($productOption->getOptions() as $optionProduct) {
                if ($optionProduct->getOptionId() == $optionId) {
                    $optionData = $optionProduct->getValues();
                    if ($optionProduct->getType() == 'field' || $optionProduct->getType() == 'area'
                        || $optionProduct->getType() == 'date'
                        || $optionProduct->getType() == 'date_time'
                        || $optionProduct->getType() == 'time') {
                        if ($optionProduct->getType() == 'date') {
                            $valueString = $option['day'] . "." . $option['month'] . "." . $option['year'];
                        } elseif ($optionProduct->getType() == 'date_time') {
                            $valueString = $option['day'] . "." . $option['month'] . "." . $option['year'] . " "
                                . $option['hour'] . ":" . $option['minute'] . " " . $option['day_part'];
                        } elseif ($optionProduct->getType() == 'time') {
                            $valueString = $option['hour'] . ":" . $option['minute'] . " " . $option['day_part'];
                        } else {
                            $valueString = $option;
                        }
                        $optionValue = $valueString;
                    } elseif (is_array($optionData)) {
                        foreach ($optionData as $data) {
                            if (!is_array($option)) {
                                if ($option == $data->getOptionTypeId()) {
                                    $optionValue = $data->getTitle();
                                }
                            } else {
                                foreach ($option as $val) {
                                    if ($val == $data->getOptionTypeId()) {
                                        $optionValue .= $data->getTitle() . ' ';
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $optionTitle         = $optionModel->getTitle();
            $additionalOptions[] = [
                'label' => $optionTitle,
                'value' => $optionValue,
            ];
        }
        if (!empty($additionalOptions)) {
            $product->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));
        }
    }

    /**
     * @param null $backUrl
     * @param null $product
     * @return \Magento\Framework\Controller\Result\Redirect|void
     */
    /*protected function afterGoBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return parent::_goBack($backUrl);
        }

        $result = [];

        if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } else {
            if ($product && !$product->getIsSalable()) {
                $result['product'] = [
                    'statusText' => __('Out of stock')
                ];
            }
        }

        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode([])
        );
    }*/
}
