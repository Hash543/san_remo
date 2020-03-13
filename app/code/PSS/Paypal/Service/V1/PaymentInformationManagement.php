<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Paypal\Service\V1;

use Magento\Framework\Exception\NoSuchEntityException;
use PSS\Paypal\Api\PaymentInformationManagementInterface;
use PSS\Paypal\Plugin\Model\CheckoutAgreements\AgreementsValidator;
use Magento\Checkout\Api\PaymentInformationManagementInterface as MagentoPaymentInformationManagementInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
/**
 * Class PaymentInformationManagement
 * @package PSS\Paypal\Service\V1
 */
class PaymentInformationManagement implements PaymentInformationManagementInterface {
    /**
     * @var MagentoPaymentInformationManagementInterface
     */
    private $paymentInformationManagement;
    /**
     * @var CartTotalRepositoryInterface
     */
    private $cartTotalRepository;
    /**
     * @var AgreementsValidator
     */
    private $agreementsValidatorSkipPlugin;
    public function __construct(
        MagentoPaymentInformationManagementInterface $paymentInformationManagement,
        CartTotalRepositoryInterface $cartTotalRepository,
        AgreementsValidator $agreementsValidatorSkipPlugin
    ) {
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->agreementsValidatorSkipPlugin = $agreementsValidatorSkipPlugin;
    }
    /**
     * @inheritdoc
     */
    public function savePaymentInformationAndGetTotals(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $this->agreementsValidatorSkipPlugin->setIsSkipValidation(true);
        $this->paymentInformationManagement->savePaymentInformation(
            $cartId,
            $paymentMethod,
            $billingAddress
        );
        try {
            return $this->cartTotalRepository->get($cartId);
        }catch (NoSuchEntityException $exception) {
            return [];
        }
    }
}