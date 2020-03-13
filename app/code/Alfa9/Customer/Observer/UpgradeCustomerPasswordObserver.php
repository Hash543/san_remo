<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Customer\Observer;

use Magento\Framework\Validator\Exception as ValidatorException;
/**
 * Class UpgradeCustomerPasswordObserver
 * @package Alfa9\Customer\Observer
 */
class UpgradeCustomerPasswordObserver extends \Magento\Customer\Observer\UpgradeCustomerPasswordObserver {

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    private $customerRegistry;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var \Magento\Framework\Validator\Factory
     */
    private $validatorFactory;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * UpgradeCustomerPasswordObserver constructor.
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Validator\Factory $validatorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->customerRegistry = $customerRegistry;
        $this->customerRepository = $customerRepository;
        $this->validatorFactory = $validatorFactory;
        $this->customerFactory = $customerFactory;
        $this->messageManager = $messageManager;
        $this->urlBuilder = $urlBuilder;
        $this->customerSession = $customerSession;
        parent::__construct($encryptor, $customerRegistry, $customerRepository);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $password = $observer->getEvent()->getData('password');
        /** @var \Magento\Customer\Model\Customer $model */
        $model = $observer->getEvent()->getData('model');
        $customer = $this->customerRepository->getById($model->getId());
        $customerSecure = $this->customerRegistry->retrieveSecureData($model->getId());

        if($this->checkCustomerAttributes($customer)) {
            if (!$this->encryptor->validateHashVersion($customerSecure->getPasswordHash(), true)) {
                $customerSecure->setPasswordHash($this->encryptor->getHash($password, true));
                $this->customerRepository->save($customer);
            }
        } else {
            $this->customerSession->setRequiredAttributesMissing(true);
            /*if (!$this->encryptor->validateHashVersion($customerSecure->getPasswordHash(), true)) {
                $passwordHash = $this->encryptor->getHash($password, true);
                $this->customerSession->setPasswordHash($passwordHash);
            }*/
        }
    }

    /**
     * Check if the customer has all their attributes
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return boolean
     */
    private function checkCustomerAttributes(\Magento\Customer\Api\Data\CustomerInterface &$customer) {
        $customerData = $this->extensibleDataObjectConverter->toNestedArray(
            $customer,
            [],
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $validator = $this->validatorFactory->createValidator('customer', 'save');
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->customerFactory->create(
            ['data' => $customerData]
        );
        if(!$validator->isValid($customerModel)) {
            $validatorError = new ValidatorException(
                null,
                null,
                $validator->getMessages()
            );
            $this->messageManager->addErrorMessage($validatorError->getMessage());
            $this->customerSession->setRequiredAttributesMissingMessage($validatorError->getMessage());
            return false;
        }
        return true;
    }
}
