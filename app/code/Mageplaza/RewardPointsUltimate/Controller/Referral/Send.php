<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Controller\Referral;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\RewardPointsUltimate\Helper\Crypt;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class Send
 * @package Mageplaza\RewardPointsUltimate\Controller\Referral
 */
class Send extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Mageplaza\RewardPointsUltimate\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Mageplaza\RewardPointsUltimate\Helper\Crypt
     */
    protected $crypt;

    /**
     * Send constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param TransportBuilder $transportBuilder
     * @param Data $helperData
     * @param Crypt $crypt
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        TransportBuilder $transportBuilder,
        Data $helperData,
        Crypt $crypt
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->transportBuilder = $transportBuilder;
        $this->helperData = $helperData;
        $this->crypt = $crypt;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($this->helperData->isEnabled() && $this->customerSession->isLoggedIn() && $data) {
            $customer = $this->customerSession->getCustomer();
            if (empty(trim($data['invitees']))) {
                $this->messageManager->addNoticeMessage(__('Please fill in the invitation field!'));

                return $this->_redirect('*/*/');
            }

            try {
                $total = 0;
                $emails = explode(',', $data['invitees']);
                if ($data['send-by'] == 'store') {
                    $sender = $this->helperData->getConfigValue(\Magento\Contact\Controller\Index::XML_PATH_EMAIL_SENDER);
                } else {
                    $sender['name'] = $customer->getName();
                    $sender['email'] = $customer->getEmail();
                }
                foreach ($emails as $email) {
                    $email = trim($email);
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $transport = $this->transportBuilder->setTemplateIdentifier($this->helperData->getInvitationEmail())
                            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $customer->getStoreId()])
                            ->setTemplateVars(
                                [
                                    'message' => htmlspecialchars($data['message']),
                                    'url'     => $this->helperData->getReferUrl($this->crypt->encrypt($customer->getId()))
                                ]
                            )
                            ->setFrom($sender)
                            ->addTo($email, '')
                            ->getTransport();

                        $transport->sendMessage();
                        $total++;
                    }
                }
                if ($total) {
                    $this->messageManager->addSuccessMessage(__('An invitation to your friends has been sent successfully!'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->_redirect('*/*/');
    }
}
