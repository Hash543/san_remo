<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Block\Widget;

use Amasty\GdprCookie\Model\CookieGroup;
use Amasty\GdprCookie\Model\CookieGroupLink;
use Amasty\GdprCookie\Model\Repository\CookieGroupsRepository;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupLink\Collection;
use Amasty\GdprCookie\Model\Repository\CookieRepository;
use Amasty\GdprCookie\Model\ResourceModel\CookieDescription\Collection as CookieDescription;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupDescription\Collection as CookieGroupDescription;
use Amasty\GdprCookie\Model\CookieManager;
use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\Config\Source\CookiePolicyBar;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Settings extends Template implements BlockInterface, IdentityInterface
{
    protected $_template = "widget/settings.phtml";

    /**
     * @var CookieGroupsRepository
     */
    private $cookieGroupsRepository;

    /**
     * @var Collection
     */
    private $groupLink;

    /**
     * @var CookieRepository
     */
    private $cookieRepository;

    /**
     * @var CookieDescription
     */
    private $cookieDescription;

    /**
     * @var CookieGroupDescription
     */
    private $cookieGroupDescription;

    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        CookieGroupsRepository $cookieGroupsRepository,
        Collection $groupLink,
        CookieRepository $cookieRepository,
        CookieDescription $cookieDescription,
        CookieGroupDescription $cookieGroupDescription,
        CookieManager $cookieManager,
        ConfigProvider $configProvider,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cookieGroupsRepository = $cookieGroupsRepository;
        $this->groupLink = $groupLink;
        $this->cookieRepository = $cookieRepository;
        $this->cookieDescription = $cookieDescription;
        $this->cookieGroupDescription = $cookieGroupDescription;
        $this->cookieManager = $cookieManager;
        $this->configProvider = $configProvider;
    }

    /**
     * @return array
     */
    public function getAllGroups()
    {
        $groups = $this->cookieGroupsRepository->getAllGroups();
        $storeId = $this->_storeManager->getStore()->getId();
        $result = [];
        $allowAll = false;
        $selectedGroups = explode(',', $this->cookieManager->getAllowCookies());

        if (in_array('0', $selectedGroups)) {
            $allowAll = true;
        }

        foreach ($groups as $group) {
            if ($group->getIsEnabled()) {
                $storeGroupDescription =
                    $this->cookieGroupDescription->getDescriptionByStore($group->getId(), $storeId);
                $groupId = $group->getId();
                $checked = in_array($groupId, $selectedGroups) || $allowAll;

                $result[$groupId]['description'] = $storeGroupDescription->getData('description')
                    ? : $group->getDescription();
                $result[$groupId]['name'] = $storeGroupDescription->getData('name') ? : $group->getName();
                $result[$groupId]['isEssential'] = (bool)$group->getIsEssential();
                $result[$groupId]['cookies'] = [];
                $result[$groupId]['checked'] = $checked || (bool)$group->getIsEssential();
                $linkedCookies = $this->groupLink->getCookiesByGroup($group->getId());

                foreach ($linkedCookies as $linkedCookie) {
                    $cookie = $this->cookieRepository->getById($linkedCookie->getData('cookie_id'));
                    $storeCookieDescription =
                        $this->cookieDescription->getDescriptionByStore($cookie->getId(), $storeId);
                    $cookieDescription = $storeCookieDescription->getData('description') ? : $cookie->getDescription();

                    array_push(
                        $result[$groupId]['cookies'],
                        ['name' => $cookie->getName(), 'description' => $cookieDescription]
                    );
                }
            }
        }

        return $result;
    }

    public function isNeedToShow()
    {
        if ($this->configProvider->getCookiePrivacyBar() !== CookiePolicyBar::CONFIRMATION) {
            return false;
        }

        return true;
    }

    public function getIdentities()
    {
        return [CookieGroupLink::CACHE_TAG, CookieGroup::CACHE_TAG];
    }

    public function getCacheLifetime()
    {
        return null;
    }

    public function getCacheTags()
    {
        return $this->getIdentities();
    }
}
