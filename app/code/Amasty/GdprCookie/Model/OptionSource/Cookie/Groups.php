<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\OptionSource\Cookie;

use Magento\Framework\Option\ArrayInterface;
use Amasty\GdprCookie\Model\Repository\CookieGroupsRepository;

class Groups implements ArrayInterface
{
    /**
     * @var CookieGroupsRepository
     */
    private $cookieGroupsRepository;

    public function __construct(
        CookieGroupsRepository $cookieGroupsRepository
    ) {
        $this->cookieGroupsRepository = $cookieGroupsRepository;
    }

    public function toOptionArray()
    {
        $groups = [['value' => "0", 'label' => __('None')]];
        $allGroups = $this->cookieGroupsRepository->getAllGroups();

        foreach ($allGroups as $group) {
            array_push($groups, ['value' => $group->getId(), 'label' => $group->getName()]);
        }

        return $groups;
    }
}
