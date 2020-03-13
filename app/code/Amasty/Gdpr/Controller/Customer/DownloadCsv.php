<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Customer;

use Amasty\Base\Helper\Utils;
use Amasty\Gdpr\Model\CustomerData;
use Magento\Customer\Controller\AbstractAccount as AbstractAccountAction;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Customer\Model\Authentication;
use Amasty\Gdpr\Model\Config;

class DownloadCsv extends AbstractAccountAction
{
    const CSV_FILE_NAME = 'personal-data.csv';

    /**
     * @var CustomerData
     */
    private $customerData;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @var Utils
     */
    private $baseHelper;

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

    public function __construct(
        Context $context,
        CustomerData $customerData,
        Session $customerSession,
        LoggerInterface $logger,
        File $fileDriver,
        Utils $baseHelper,
        Authentication $authentication,
        FormKeyValidator $formKeyValidator,
        Config $configProvider
    ) {
        parent::__construct($context);
        $this->customerData = $customerData;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->fileDriver = $fileDriver;
        $this->baseHelper = $baseHelper;
        $this->formKeyValidator = $formKeyValidator;
        $this->authentication = $authentication;
        $this->configProvider = $configProvider;
    }

    public function execute()
    {
        $errorMessage = '';

        if (!$this->configProvider->isAllowed(Config::DOWNLOAD)) {
            $errorMessage = __('Access denied.');
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $errorMessage = __('Invalid Form Key. Please refresh the page.');
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
            $data = $this->customerData->getPersonalData($this->customerSession->getId());

            /** @var \Magento\Framework\App\Response\Http $response */
            $response = $this->getResponse();
            $response->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', 'text/csv', true)
                ->setHeader('Content-Disposition', 'attachment; filename="' . self::CSV_FILE_NAME . '"', true)
                ->setHeader('Last-Modified', date('r'), true)
                ->sendHeaders();
            $resource = $this->fileDriver->fileOpen('php://output', 'w');

            foreach ($data as $row) {
                $this->fileDriver->filePutCsv($resource, $row);
            }
            $this->baseHelper->_exit();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error has occurred'));
            $this->logger->critical($e);
        }

        $this->_redirect('*/*/settings');
    }
}
