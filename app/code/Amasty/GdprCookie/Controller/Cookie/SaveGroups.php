<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Controller\Cookie;

use Amasty\GdprCookie\Model\CookieManager;
use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupLink\Collection as LinkCollection;
use Amasty\GdprCookie\Model\Repository\CookieRepository;
use Amasty\GdprCookie\Model\Repository\CookieGroupsRepository;
use Amasty\GdprCookie\Model\CookieConsent;
use Amasty\GdprCookie\Model\CookieConsentLogger;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;

class SaveGroups extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ConfigProvider
     */
    private $config;

    /**
     * @var LinkCollection
     */
    private $linkCollection;

    /**
     * @var CookieRepository
     */
    private $cookieRepository;

    /**
     * @var CookieConsentLogger
     */
    private $consentLogger;

    /**
     * @var CookieGroupsRepository
     */
    private $groupsRepository;

    public function __construct(
        Context $context,
        CookieManager $cookieManager,
        Session $session,
        LinkCollection $linkCollection,
        CookieRepository $cookieRepository,
        CookieGroupsRepository $groupsRepository,
        CookieConsentLogger $consentLogger,
        ConfigProvider $config
    ) {
        parent::__construct($context);

        $this->cookieManager = $cookieManager;
        $this->session = $session;
        $this->config = $config;
        $this->linkCollection = $linkCollection;
        $this->cookieRepository = $cookieRepository;
        $this->consentLogger = $consentLogger;
        $this->groupsRepository = $groupsRepository;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $rejectedCookies = [];

        if (isset($data['groups'])) {
            $groups = implode(',', $data['groups']);

            $allGroups = $this->groupsRepository->getAllGroups();
            $selectedGroups = [];

            foreach ($allGroups as $groupId => $group) {
                if (!in_array($groupId, $data['groups'])) {
                    array_push($selectedGroups, $groupId);
                }
            }
            $rejectedCookies = [];

            foreach ($selectedGroups as $group) {
                $cookies = $this->linkCollection->getCookiesByGroup($group);

                foreach ($cookies as $cookie) {
                    array_push(
                        $rejectedCookies,
                        $this->cookieRepository->getById($cookie->getData('cookie_id'))->getName()
                    );
                }
            }

            if ($customerId = $this->session->getCustomerId()) {
                $groupsLog = $this->prepareGroupsStatus($data['groups']);
                $this->consentLogger->logCookieConsent(
                    $customerId,
                    CookieConsent::CONFIRMATION_CONSENT,
                    $groupsLog);
            }
            $this->messageManager->addSuccessMessage(__('You saved your cookie settings!'));
            $this->cookieManager->deleteCookies($rejectedCookies);
            $this->cookieManager->setIsAllowCookies($groups);
        }
    }

    public function prepareGroupsStatus($groups)
    {
        $result = '';

        foreach ($groups as $group) {
            $groupName = $this->groupsRepository->getById($group)->getName();
            $result .= '<strong>' . $groupName . ':</strong> Allowed<br/>';
        }

        return rtrim($result, '<br/>');
    }
}
