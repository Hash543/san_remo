<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Amasty\GdprCookie\Model\CookieFactory;
use Amasty\GdprCookie\Model\CookieGroupFactory;
use Amasty\GdprCookie\Model\Repository\CookieGroupsRepository;
use Amasty\GdprCookie\Model\Repository\CookieRepository;
use Amasty\GdprCookie\Model\CookieGroupLinkFactory;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupLink as LinkResource;
use Magento\Setup\Exception;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;

class InstallData implements InstallDataInterface
{
    private $cookiesByGroups = [
        'Essential' => [
            'Cookies' => [
                'PHPSESSID' => "To store the logged in user's username and a 128bit encrypted key."
                    . "This information is required to allow a user to stay logged in to a web site"
                    . "without needing to submit their username and password for each page visited."
                    . " Without this cookie, a user is unabled to proceed to areas of the web site"
                    . " that require authenticated access.",
                'private_content_version' => "Appends a random, unique number and time to pages with customer content"
                    . " to prevent them from being cached on the server.",
                'persistent_shopping_cart' => "Stores the key (ID) of persistent cart to make it possible to"
                    ." restore the cart for an anonymous shopper.",
                'form_key' => "A security measure that appends a random string to all form submissions"
                    . " to protect the data from Cross-Site Request Forgery (CSRF).",
                'store' => "Tracks the specific store view / locale selected by the shopper.",
                'login_redirect' => "Preserves the destination page the customer was navigating"
                    . " to before being directed to log in.",
                'mage-messages' => "Tracks error messages and other notifications that are shown to the user,"
                    . " such as the cookie consent message, and various error messages, The message is"
                    . " deleted from the cookie after it is shown to the shopper.",
                'mage-cache-storage' => "Local storage of visitor-specific content that enables e-commerce functions.",
                'mage-cache-storage-section-invalidation' => "Forces local storage of specific content sections"
                    . " that should be invalidated.",
                'mage-cache-sessid' => "The value of this cookie triggers the cleanup of local cache storage.",
                'product_data_storage' => "Stores configuration for product data related to Recently Viewed"
                    . " / Compared Products.",
                'user_allowed_save_cookie' => "Indicates if the shopper allows cookies to be saved.",
                'mage-translation-storage' => "Stores translated content when requested by the shopper.",
                'mage-translation-file-version' => "Stores the file version of translated content."
            ],
            'Description' => "Necessary cookies enable core functionality of the website. Without these cookies the website"
                . " can not function properly. They help to make a website usable by enabling basic functionality.",
            'Essential' => true,
            'Enabled' => true
        ],
        'Marketing' => [
            'Cookies' => [
                'section_data_ids' => "Stores customer-specific information related to shopper-initiated actions"
                    . " such as display wish list, checkout information, etc.",
                'recently_viewed_product' => "Stores product IDs of recently viewed products for easy navigation.",
                'recently_viewed_product_previous' => "Stores product IDs of recently previously viewed"
                    . " products for easy navigation.",
                'recently_compared_product' => "Stores product IDs of recently compared products.",
                'recently_compared_product_previous' => "Stores product IDs of previously compared"
                    . " products for easy navigation."
            ],
            'Description' => "Marketing cookies are used to track and collect visitors actions"
                . " on the website. Cookies store user data and behaviour information, which"
                . " allows advertising services to target more audience groups. Also more customized"
                . " user experience can be provided according to collected information.",
            'Essential' => false,
            'Enabled' => true
        ],
        'Google Analytics' => [
            'Cookies' => [
                '_ga' => "Used to distinguish users.",
                '_gid' => "Used to distinguish users.",
                '_gat' => "Used to throttle request rate."
            ],
            'Description' => "A set of cookies to collect information and report about website usage statistics"
                . " without personally identifying individual visitors to Google.",
            'Essential' => false,
            'Enabled' => true
        ]
    ];

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CookieFactory
     */
    private $cookieFactory;

    /**
     * @var CookieRepository
     */
    private $cookieRepository;

    /**
     * @var CookieGroupFactory
     */
    private $cookieGroupFactory;

    /**
     * @var CookieGroupsRepository
     */
    private $cookieGroupsRepository;

    /**
     * @var LinkResource
     */
    private $groupLink;

    /**
     * @var CookieGroupLinkFactory
     */
    private $linkFactory;

    /**
     * @var State
     */
    private $appState;

    public function __construct(
        PageFactory $pageFactory,
        ScopeConfigInterface $scopeConfig,
        CookieFactory $cookieFactory,
        CookieGroupFactory $cookieGroupFactory,
        CookieGroupsRepository $cookieGroupsRepository,
        CookieRepository $cookieRepository,
        LinkResource $groupLink,
        CookieGroupLinkFactory $linkFactory,
        State $appState
    ) {
        $this->pageFactory = $pageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->cookieFactory = $cookieFactory;
        $this->cookieRepository = $cookieRepository;
        $this->cookieGroupFactory = $cookieGroupFactory;
        $this->cookieGroupsRepository = $cookieGroupsRepository;
        $this->groupLink = $groupLink;
        $this->linkFactory = $linkFactory;
        $this->appState = $appState;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, [$this, 'installModuleData']);
    }

    /**
     * @return void
     */
    public function installModuleData()
    {
        $this->installCmsPagesData();
        $this->installCookieData();

        // copy cookies data from Amasty_Gdpr
        if ($gdprConfigCookies = $this->scopeConfig->getValue('amasty_gdpr/cookie_policy/confirmation_cookies')) {
            $cookies = preg_split('/\n|\r\n?/', $gdprConfigCookies);

            foreach ($cookies as $cookieName) {
                $cookie = $this->cookieFactory->create();
                $cookie->setName($cookieName);
                $cookie->setDescription('Cookie from GDPR');

                try {
                    $this->cookieRepository->save($cookie);
                } catch (\Exception $e) {
                    //do nothing
                }
            }
        }
    }

    private function installCmsPagesData()
    {
        $page = $this->pageFactory->create();

        if (!$page->checkIdentifier('cookie-settings', 0)) {
            try {
                $page->setTitle('Cookie Settings')
                    ->setIdentifier('cookie-settings')
                    ->setIsActive(true)
                    ->setPageLayout('1column')
                    ->setContent(
                        '{{widget type="Amasty\GdprCookie\Block\Widget\Settings" ' .
                        'type_name="Amasty Cookie Settings"}}'
                    )->setStoreId(["0"])
                    ->save();
            } catch (\Exception $e) {
                null;
            }
        }

        if (!$page->checkIdentifier('cookie-policy', 0)) {
            try {
                $page = $this->pageFactory->create();

                $page->setTitle('Cookie Policy')
                    ->setIdentifier('cookie-policy')
                    ->setContent($this->getCookiePolicyContent())
                    ->setPageLayout('1column')
                    ->setStoreId(["0"])
                    ->save();
            } catch (\Exception $e) {
                null;
            }
        }
    }

    /**
     * @return string
     */
    private function getCookiePolicyContent()
    {
        return '<p>This site, like many others, uses small files called cookies to help us customize your experience.
                    Find out more about cookies and how you can control them.</p>
                    <p></p>
                    <p>What is a cookie?</p>
                    <p></p>
                    <p>A cookie is a small file that can be placed on your device that allows us
                    to recognise and remember you. It is sent to your browser and stored on your computer’s
                    hard drive or tablet or mobile device. When you visit our sites, we may collect information
                    from you automatically through cookies or similar technology.</p>
                    <p></p>
                    <p>How do we use cookies?</p>
                    <p></p>
                    <p>We use cookies in a range of ways to improve your experience on our site, including:</p>
                    <p></p>
                    <p>Keeping you signed in;</p>
                    <p>Understanding how you use our site;</p>
                    <p>Showing you content that is relevant to you;</p>
                    <p>Showing you products and services that are relevant to you.;</p>';
    }

    private function installCookieData()
    {
        foreach ($this->cookiesByGroups as $groupName => $groupData) {
            $cookieGroup = $this->cookieGroupFactory->create();
            $cookieGroup->setName($groupName);
            $cookieGroup->setDescription($groupData['Description']);
            $cookieGroup->setIsEnabled($groupData['Enabled']);
            $cookieGroup->setIsEssential($groupData['Essential']);

            $this->cookieGroupsRepository->save($cookieGroup);
            $groupId = $cookieGroup->getId();

            foreach ($groupData['Cookies'] as $name => $description) {
                $cookie = $this->cookieFactory->create();
                $cookie->setName($name);
                $cookie->setDescription($description);

                $this->cookieRepository->save($cookie);
                $cookieId = $cookie->getId();

                $link = $this->linkFactory->create();
                $link->setData([
                    'cookie_id' => $cookieId,
                    'group_id' => $groupId
                ]);
                $this->groupLink->save($link);
            }
        }
    }
}
