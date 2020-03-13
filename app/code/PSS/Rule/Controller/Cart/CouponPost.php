<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Rule\Controller\Cart;

use Magento\Framework\Exception\LocalizedException;
use PSS\CRM\Helper\PromotionService as HelperPromotion;
/**
 * Override this Class to search in the werbservice the coupon code if in case does not exists still
 * in the Magento Store
 */
class CouponPost extends \Magento\Checkout\Controller\Cart\CouponPost {
    /**
     * @var \PSS\CRM\Api\PromotionRepositoryInterface
     */
    protected $promotionRepository;
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $ruleCollectionFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var \Magento\SalesRule\Api\CouponRepositoryInterface
     */
    protected $couponRepository;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * CouponPost constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param \Magento\SalesRule\Api\CouponRepositoryInterface $couponRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \PSS\CRM\Api\PromotionRepositoryInterface $promotionRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\SalesRule\Api\CouponRepositoryInterface $couponRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \PSS\CRM\Api\PromotionRepositoryInterface $promotionRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->timezone = $timezone;
        $this->promotionRepository = $promotionRepository;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->couponRepository = $couponRepository;
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart, $couponFactory, $quoteRepository);
    }
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $couponCode = $this->getRequest()->getParam('remove') == 1
            ? ''
            : trim($this->getRequest()->getParam('coupon_code'));

        $cartQuote = $this->cart->getQuote();
        $oldCouponCode = $cartQuote->getCouponCode();

        $codeLength = strlen($couponCode);
        if (!$codeLength && !strlen($oldCouponCode)) {
            return $this->_goBack();
        }

        try {
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

            $itemsCount = $cartQuote->getItemsCount();
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                $this->quoteRepository->save($cartQuote);
            }

            if ($codeLength) {
                $escaper = $this->_objectManager->get(\Magento\Framework\Escaper::class);
                $coupon = $this->couponFactory->create();
                $coupon->load($couponCode, 'code');
                if (!$itemsCount) {
                    if ($isCodeLengthValid && $coupon->getId()) {
                        $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
                        $this->messageManager->addSuccessMessage(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    } else {
                        $this->messageManager->addErrorMessage(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    }
                } else {
                    if ($isCodeLengthValid && $coupon->getId() && $couponCode == $cartQuote->getCouponCode()) {
                        $this->messageManager->addSuccessMessage(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    } else if($this->createCouponFromWebservice($couponCode)){
                        $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                        $cartQuote->setCouponCode($couponCode)->collectTotals();
                        $this->quoteRepository->save($cartQuote);
                        $this->messageManager->addSuccessMessage(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    } else {
                        $this->messageManager->addErrorMessage(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    }
                }
            } else {
                $this->messageManager->addSuccessMessage(__('You canceled the coupon code.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We cannot apply the coupon code.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }

        return $this->_goBack();
    }

    /**
     * @param string $coupon
     * @return boolean
     * @throws LocalizedException
     */
    private function createCouponFromWebservice($coupon) {

        $customerId = $this->_checkoutSession->getQuote()->getCustomerId();
        $response = $this->promotionRepository->getCoupon($coupon);
        if($customerId && isset($response['a:vales']['a:Vale']) ) {
            $vale = $response['a:vales']['a:Vale'];
            $currentDate = $this->timezone->date()->format('Y-m-d H:i:s');
            $initDate = $this->timezone->date(new \DateTime(HelperPromotion::getFieldWS($vale['a:FechaInicio'])))->format('Y-m-d H:i:s');
            $endDate = $this->timezone->date(new \DateTime(HelperPromotion::getFieldWS($vale['a:FechaFin'])))->format('Y-m-d H:i:s');
            if($currentDate < $initDate || $currentDate > $endDate) {
                throw new LocalizedException(__('The coupon has invalid date.'));
            }
            $customerCrmId = $this->getCustomerCrmId($customerId);
            $clientCodeCrmPromotion = HelperPromotion::getFieldWS($vale['a:CodigoCliente']);
            if( $clientCodeCrmPromotion && $clientCodeCrmPromotion !== $customerCrmId) {
                throw new LocalizedException(__('The coupon belongs another customer.'));
            }
            if(!HelperPromotion::getFieldWS($vale['a:Estado'])) {
                throw new LocalizedException(__('The coupon is invalid'));
            }
            if($rule = $this->getPromotionRule($vale['a:PromocionConsumo'])) {
                /** @var \Magento\SalesRule\Model\Coupon $coupon */
                $coupon = $this->couponFactory->create();
                $coupon->setExpirationDate($endDate)
                    ->setCreatedAt($currentDate)
                    ->setRuleId($rule->getRuleId())
                    ->setCode(HelperPromotion::getFieldWS($vale['a:CodigoDeVale']))
                    ->setType(\Magento\SalesRule\Model\Coupon::TYPE_GENERATED)
                    ->setUsageLimit(1)
                    ->setTimesUsed(0)
                    ->setIsPrimary(null)
                    ->setData(\PSS\Rule\Helper\Data::CUSTOMER_ID, $customerId)
                    ->setData(\PSS\Rule\Helper\Data::ID_TICKET_DESTINY, HelperPromotion::getFieldWS($vale['a:TicketDestino']))
                    ->setData(\PSS\Rule\Helper\Data::ID_TICKET_ORIGIN, HelperPromotion::getFieldWS($vale['a:TicketOrigen']));

                $this->couponRepository->save($coupon);
                return true;
            }
        }

        return false;
    }
    /**
     * @param string $crmId
     * @return \Magento\SalesRule\Model\Rule|null
     */
    private function getPromotionRule($crmId) {
        /** @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection $collectionRule */
        $collectionRule = $this->ruleCollectionFactory->create();
        $collectionRule = $collectionRule->addFieldToFilter(\PSS\Rule\Helper\Data::CRM_ID, ['eq' => $crmId])->load();
        /** @var \Magento\SalesRule\Model\Rule $rule */
        $rule = $collectionRule->getFirstItem();
        if($rule && $rule->getId()) {
            return $rule;
        }
        return null;
    }

    /**
     * @param $customerId
     * @return null
     */
    private function getCustomerCrmId($customerId) {
        $crmId = null;
        try {
            $customer = $this->customerRepository->getById($customerId);
            if($crmIdExtension = $customer->getCustomAttribute('id_crm')) {
                $crmId = $crmIdExtension->getValue();
            }
        }catch (\Exception $exception) {
            $this->logger->warning($exception->getMessage());
        }
        return $crmId;
    }
}