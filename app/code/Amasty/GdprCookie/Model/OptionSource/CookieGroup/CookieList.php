<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\OptionSource\CookieGroup;

use Amasty\GdprCookie\Api\Data\CookieInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\App\RequestInterface;
use Amasty\GdprCookie\Model\Repository\CookieRepository;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroupLink\Collection;

class CookieList implements ArrayInterface
{
    /**
     * @var CookieRepository
     */
    private $cookieRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Collection
     */
    private $linkCollection;

    public function __construct(
        CookieRepository $cookieRepository,
        RequestInterface $request,
        Collection $linkCollection
    ) {
        $this->cookieRepository = $cookieRepository;
        $this->request = $request;
        $this->linkCollection = $linkCollection;
    }

    public function toOptionArray()
    {
        $id = $this->request->getParam('id');
        $result = [];

        if ($id) {
            $assignedCookies = $this->linkCollection->getCookiesByGroup($id);

            foreach ($assignedCookies as $assignedCookie) {
                /** @var CookieInterface $cookieModel */
                $cookieModel = $this->cookieRepository->getById($assignedCookie->getData('cookie_id'));
                array_push($result, ['value' => $cookieModel->getId(), 'label' => $cookieModel->getName()]);
            }
        }
        $usedIds = [];
        $usedCookies = $this->linkCollection->getItems();

        foreach ($usedCookies as $cookie) {
            array_push($usedIds, $cookie->getData('cookie_id'));
        }
        $cookies = $this->cookieRepository->getFreeCookies($usedIds);

        /** @var CookieInterface $cookie */
        foreach ($cookies as $cookie) {
            array_push($result, ['value' => $cookie->getId(), 'label' => $cookie->getName()]);
        }

        return $result;
    }
}
