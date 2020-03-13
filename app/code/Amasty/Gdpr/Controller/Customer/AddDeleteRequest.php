<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Customer;

use Amasty\Gdpr\Api\DeleteRequestRepositoryInterface;
use Amasty\Gdpr\Model\ActionLogger;
use Amasty\Gdpr\Model\DeleteRequest;
use Amasty\Gdpr\Model\DeleteRequestFactory;
use Magento\Customer\Controller\AbstractAccount as AbstractAccountAction;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Customer\Model\Authentication;
use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory;
use Amasty\Gdpr\Model\DeleteRequest\Notifier;

class AddDeleteRequest extends AbstractAccountAction
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DeleteRequestRepositoryInterface
     */
    private $deleteRequestRepository;

    /**
     * @var DeleteRequestFactory
     */
    private $deleteRequestFactory;

    /**
     * @var ActionLogger
     */
    private $actionLogger;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var Authentication
     */
    private $authentication;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var CollectionFactory
     */
    private $deleteRequestCollectionFactory;

    /**
     * @var Notifier
     */
    private $notifier;

    public function __construct(
        Context $context,
        Session $customerSession,
        LoggerInterface $logger,
        DeleteRequestFactory $deleteRequestFactory,
        DeleteRequestRepositoryInterface $deleteRequestRepository,
        ActionLogger $actionLogger,
        FormKeyValidator $formKeyValidator,
        Authentication $authentication,
        Config $configProvider,
        CollectionFactory $deleteRequestCollectionFactory,
        Notifier $notifier
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->deleteRequestRepository = $deleteRequestRepository;
        $this->deleteRequestFactory = $deleteRequestFactory;
        $this->actionLogger = $actionLogger;
        $this->formKeyValidator = $formKeyValidator;
        $this->authentication = $authentication;
        $this->configProvider = $configProvider;
        $this->deleteRequestCollectionFactory = $deleteRequestCollectionFactory;
        $this->notifier = $notifier;
    }

    public function execute()
    {
        $errorMessage = '';

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $errorMessage = __('Invalid Form Key. Please refresh the page.');
        }

        if (!$this->configProvider->isAllowed(Config::DELETE)) {
            $errorMessage = __('Access denied.');
        }

        if ($errorMessage) {
            $this->messageManager->addErrorMessage($errorMessage);
            $this->_redirect('*/*/settings');
            return;
        }

        $customerId = $this->customerSession->getCustomerId();
        $customerPass = $this->getRequest()->getParam('current_password');

        try {
            $this->authentication->authenticate($customerId, $customerPass);
        } catch (\Magento\Framework\Exception\AuthenticationException $e) {
            $this->messageManager->addErrorMessage(__('Wrong Password. Please recheck it.'));
            $this->_redirect('*/*/settings');
            return;
        }

        try {
            $deleteRequests = $this->deleteRequestCollectionFactory->create();

            if ($deleteRequests->addFieldToFilter('customer_id', $customerId)->getSize()) {
                $this->messageManager->addErrorMessage(
                    __('Your delete account request is awaiting for the review by the administrator.')
                );
            } else {
                /** @var DeleteRequest $request */
                $request = $this->deleteRequestFactory->create();
                $request->setCustomerId($customerId);
                $this->deleteRequestRepository->save($request);
                $this->actionLogger->logAction('delete_request_submitted', $request->getCustomerId());
                if ($this->configProvider->isAdminDeleteNotificationEnabled()) {
                    $this->notifier->notifyAdmin($customerId);
                }
                $this->messageManager->addSuccessMessage(
                    __('Thank you, your account delete request was recorded.')
                );
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('An error has occurred'));
            $this->logger->critical($exception);
        }

        $this->_redirect('*/*/settings');
    }
}
