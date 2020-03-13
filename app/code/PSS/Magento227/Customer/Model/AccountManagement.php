<?php
/**
 * AccountManagement
 *
 * @copyright Copyright © 2019 Alfa9 Servicios Web, S.L.. All rights reserved.
 * @author    xsanz@alfa9.com
 */

namespace PSS\Magento227\Customer\Model;


use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\ValidationResultsInterfaceFactory;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\Customer\CredentialsValidator;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\Metadata\Validator;
use Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Phrase;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Framework\App\ResourceConnection;

class AccountManagement extends \Magento\Customer\Model\AccountManagement
{

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Customer\Api\Data\ValidationResultsInterfaceFactory
     */
    private $validationResultsDataFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadataService;

    /**
     * @var PsrLogger
     */
    protected $logger;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var ConfigShare
     */
    private $configShare;

    /**
     * @var StringHelper
     */
    protected $stringHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var SaveHandlerInterface
     */
    private $saveHandler;

    /**
     * @var CollectionFactory
     */
    private $visitorCollectionFactory;

    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var CustomerViewHelper
     */
    protected $customerViewHelper;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var CustomerModel
     */
    protected $customerModel;

    /**
     * @var AuthenticationInterface
     */
    protected $authentication;

    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var \Magento\Eav\Model\Validator\Attribute\Backend
     */
    private $eavValidator;

    /**
     * @var CredentialsValidator
     */
    private $credentialsValidator;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(CustomerFactory $customerFactory, ManagerInterface $eventManager, StoreManagerInterface $storeManager, Random $mathRandom, Validator $validator, ValidationResultsInterfaceFactory $validationResultsDataFactory, AddressRepositoryInterface $addressRepository, CustomerMetadataInterface $customerMetadataService, CustomerRegistry $customerRegistry, PsrLogger $logger, Encryptor $encryptor, ConfigShare $configShare, StringHelper $stringHelper, CustomerRepositoryInterface $customerRepository, ScopeConfigInterface $scopeConfig, TransportBuilder $transportBuilder, DataObjectProcessor $dataProcessor, Registry $registry, CustomerViewHelper $customerViewHelper, DateTime $dateTime, CustomerModel $customerModel, ObjectFactory $objectFactory, ExtensibleDataObjectConverter $extensibleDataObjectConverter, CredentialsValidator $credentialsValidator = null, DateTimeFactory $dateTimeFactory = null, AccountConfirmation $accountConfirmation = null, SessionManagerInterface $sessionManager = null, SaveHandlerInterface $saveHandler = null, CollectionFactory $visitorCollectionFactory = null, SearchCriteriaBuilder $searchCriteriaBuilder = null)
    {
        $this->customerFactory = $customerFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->mathRandom = $mathRandom;
        $this->validator = $validator;
        $this->validationResultsDataFactory = $validationResultsDataFactory;
        $this->addressRepository = $addressRepository;
        $this->customerMetadataService = $customerMetadataService;
        $this->customerRegistry = $customerRegistry;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->configShare = $configShare;
        $this->stringHelper = $stringHelper;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->dataProcessor = $dataProcessor;
        $this->registry = $registry;
        $this->customerViewHelper = $customerViewHelper;
        $this->dateTime = $dateTime;
        $this->customerModel = $customerModel;
        $this->objectFactory = $objectFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->credentialsValidator =
            $credentialsValidator ?: ObjectManager::getInstance()->get(CredentialsValidator::class);
        $this->dateTimeFactory = $dateTimeFactory ?: ObjectManager::getInstance()->get(DateTimeFactory::class);
        $this->accountConfirmation = $accountConfirmation ?: ObjectManager::getInstance()
            ->get(AccountConfirmation::class);
        $this->sessionManager = $sessionManager
            ?: ObjectManager::getInstance()->get(SessionManagerInterface::class);
        $this->saveHandler = $saveHandler
            ?: ObjectManager::getInstance()->get(SaveHandlerInterface::class);
        $this->visitorCollectionFactory = $visitorCollectionFactory
            ?: ObjectManager::getInstance()->get(CollectionFactory::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder
            ?: ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
        $this->resourceConnection = ObjectManager::getInstance()->get(ResourceConnection::class);
        parent::__construct($customerFactory, $eventManager, $storeManager, $mathRandom, $validator, $validationResultsDataFactory, $addressRepository, $customerMetadataService, $customerRegistry, $logger, $encryptor, $configShare, $stringHelper, $customerRepository, $scopeConfig, $transportBuilder, $dataProcessor, $registry, $customerViewHelper, $dateTime, $customerModel, $objectFactory, $extensibleDataObjectConverter, $credentialsValidator, $dateTimeFactory, $accountConfirmation, $sessionManager, $saveHandler, $visitorCollectionFactory, $searchCriteriaBuilder);
    }

    /**
     * @param int $customerId
     * @param string $resetPasswordLinkToken
     * @return bool
     * @throws ExpiredException
     * @throws InputException
     * @throws InputMismatchException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    private function validateResetPasswordToken($customerId, $resetPasswordLinkToken)
    {
        if (empty($customerId) || $customerId < 0) {
            //Looking for the customer.
            $customerId = $this->matchCustomerByRpToken($resetPasswordLinkToken)
                ->getId();
        }
        if (!is_string($resetPasswordLinkToken) || empty($resetPasswordLinkToken)) {
            $params = ['fieldName' => 'resetPasswordLinkToken'];
            throw new InputException(__('%fieldName is a required field.', $params));
        }

        $customerSecureData = $this->customerRegistry->retrieveSecureData($customerId);
        $rpToken = $customerSecureData->getRpToken();
        $rpTokenCreatedAt = $customerSecureData->getRpTokenCreatedAt();

        if (!Security::compareStrings($rpToken, $resetPasswordLinkToken)) {
            throw new InputMismatchException(__('Reset password token mismatch.'));
        } elseif ($this->isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)) {
            throw new ExpiredException(__('Reset password token expired.'));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resetPassword($email, $resetToken, $newPassword)
    {
        if (!$email) {
            $customer = $this->matchCustomerByRpToken($resetToken);
            $email = $customer->getEmail();
        } else {
            $customer = $this->customerRepository->get($email);
        }
        //Validate Token and new password strength
        $this->validateResetPasswordToken($customer->getId(), $resetToken);
        $this->credentialsValidator->checkPasswordDifferentFromEmail(
            $email,
            $newPassword
        );
        $this->checkPasswordStrength($newPassword);
        //Update secure data
        /*$customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerSecure->setRpToken(null);
        $customerSecure->setRpTokenCreatedAt(null);
        $customerSecure->setPasswordHash($this->createPasswordHash($newPassword));*/
        $this->getAuthentication()->unlock($customer->getId());
        //FIX https://magento.stackexchange.com/questions/248574/something-went-wrong-while-saving-the-new-password-magento-2
        $this->destroyCustomerSessions($customer->getId());
        $this->sessionManager->destroy();
        //$this->customerRepository->save($customer);
        //Todo: Inserted value manually because the Model has some fields required null
        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            $this->resourceConnection->getTableName('customer_entity'),
            ['rp_token' => null, 'rp_token_created_at' => null, 'password_hash' => $this->createPasswordHash($newPassword)],
            ['entity_id = ?' => (int)$customer->getId()]
        );
        return true;
    }
    /**
     * {@inheritdoc}
     */
    public function changeResetPasswordLinkToken($customer, $passwordLinkToken)
    {
        if (!is_string($passwordLinkToken) || empty($passwordLinkToken)) {
            throw new InputException(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['value' => $passwordLinkToken, 'fieldName' => 'password reset token']
                )
            );
        }
        if (is_string($passwordLinkToken) && !empty($passwordLinkToken)) {
            //Todo: Inserted value manually because the Model has some fields required null
            $connection = $this->resourceConnection->getConnection();
            $rpTokenCreatedAt = $this->dateTimeFactory->create()->format(DateTime::DATETIME_PHP_FORMAT);
            $connection->update(
                $this->resourceConnection->getTableName('customer_entity'),
                ['rp_token' => $passwordLinkToken, 'rp_token_created_at' => $rpTokenCreatedAt],
                ['entity_id = ?' => (int)$customer->getId()]
            );

            $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
            $customerSecure->setRpToken($passwordLinkToken);
            $customerSecure->setRpTokenCreatedAt(
                $this->dateTimeFactory->create()->format(DateTime::DATETIME_PHP_FORMAT)
            );
            /*$this->customerRepository->save($customer);*/
        }
        return true;
    }
    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    private function getAuthentication()
    {
        if (!($this->authentication instanceof AuthenticationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Customer\Model\AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }

    /**
     * @param string $rpToken
     * @return CustomerInterface
     * @throws ExpiredException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    private function matchCustomerByRpToken(string $rpToken): CustomerInterface
    {

        $this->searchCriteriaBuilder->addFilter(
            'rp_token',
            $rpToken
        );
        $this->searchCriteriaBuilder->setPageSize(1);
        $found = $this->customerRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        if ($found->getTotalCount() > 1) {
            //Failed to generated unique RP token
            throw new ExpiredException(
                new Phrase('Reset password token expired.')
            );
        }
        if ($found->getTotalCount() === 0) {
            //Customer with such token not found.
            throw NoSuchEntityException::singleField(
                'rp_token',
                $rpToken
            );
        }

        //Unique customer found.
        return $found->getItems()[0];
    }


    /**
     * Destroy all active customer sessions by customer id (current session will not be destroyed).
     * Customer sessions which should be deleted are collecting  from the "customer_visitor" table considering
     * configured session lifetime.
     *
     * @param string|int $customerId
     * @return void
     */
    private function destroyCustomerSessions($customerId)
    {
        $sessionLifetime = $this->scopeConfig->getValue(
            \Magento\Framework\Session\Config::XML_PATH_COOKIE_LIFETIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $dateTime = $this->dateTimeFactory->create();
        $activeSessionsTime = $dateTime->setTimestamp($dateTime->getTimestamp() - $sessionLifetime)
            ->format(DateTime::DATETIME_PHP_FORMAT);
        /** @var \Magento\Customer\Model\ResourceModel\Visitor\Collection $visitorCollection */
        $visitorCollection = $this->visitorCollectionFactory->create();
        $visitorCollection->addFieldToFilter('customer_id', $customerId);
        $visitorCollection->addFieldToFilter('last_visit_at', ['from' => $activeSessionsTime]);
        $visitorCollection->addFieldToFilter('session_id', ['neq' => $this->sessionManager->getSessionId()]);
        /** @var \Magento\Customer\Model\Visitor $visitor */
        foreach ($visitorCollection->getItems() as $visitor) {
            $sessionId = $visitor->getSessionId();
            $this->saveHandler->destroy($sessionId);
        }
    }


}