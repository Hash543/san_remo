<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Paypal\Service\V1;

use Magento\Framework\Exception\NoSuchEntityException;
use PSS\Paypal\Api\GuestPaymentInformationManagementInterface;
use PSS\Paypal\Plugin\Model\CheckoutAgreements\AgreementsValidator;
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface as MagentoGuestPaymentManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\GuestCartTotalRepositoryInterface;
/**
 * Class GuestPaymentInformationManagement
 * @package PSS\Paypal\Service\V1
 */
class GuestPaymentInformationManagement implements GuestPaymentInformationManagementInterface{
    /**
     * @var MagentoGuestPaymentManagementInterface
     */
    private $guestPaymentInformationManagement;
    /**
     * @var GuestCartTotalRepositoryInterface
     */
    private $guestCartTotalRepository;
    /**
     * @var AgreementsValidator
     */
    private $agreementsValidatorSkipPlugin;

    /**
     * GuestPaymentInformationManagement constructor.
     * @param MagentoGuestPaymentManagementInterface $guestPaymentInformationManagement
     * @param GuestCartTotalRepositoryInterface $guestCartTotalRepository
     * @param AgreementsValidator $agreementsValidatorSkipPlugin
     */
    public function __construct(
        MagentoGuestPaymentManagementInterface $guestPaymentInformationManagement,
        GuestCartTotalRepositoryInterface $guestCartTotalRepository,
        AgreementsValidator $agreementsValidatorSkipPlugin
    ) {
        $this->guestPaymentInformationManagement = $guestPaymentInformationManagement;
        $this->guestCartTotalRepository = $guestCartTotalRepository;
        $this->agreementsValidatorSkipPlugin = $agreementsValidatorSkipPlugin;
    }
    /**
     * @inheritdoc
     */
    public function savePaymentInformationAndGetTotals(
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $this->agreementsValidatorSkipPlugin->setIsSkipValidation(true);
        $this->guestPaymentInformationManagement->savePaymentInformation(
            $cartId,
            $email,
            $paymentMethod,
            $billingAddress
        );
        try {
            return $this->guestCartTotalRepository->get($cartId);
        }catch (NoSuchEntityException $exception) {
            return [];
        }
    }
}