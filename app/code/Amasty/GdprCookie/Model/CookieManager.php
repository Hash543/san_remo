<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model;

use Magento\PageCache\Model\Cache\Type as CacheManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;

class CookieManager
{
    /**#@+*/
    const ALLOW_COOKIES = 'amcookie_allowed';

    const ALLOWED_ALL = '0';

    /**#@-*/

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var CacheManager
     */
    private $cache;

    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        CacheManager $cache
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
        $this->cache = $cache;
    }

    /**
     * @param string $status
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function setIsAllowCookies($cookies)
    {
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain())
            ->setDurationOneYear();

        try {
            $this->cookieManager->setPublicCookie(self::ALLOW_COOKIES, $cookies, $cookieMetadata);
            $this->cache->clean(
                \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                [CookieGroupLink::CACHE_TAG, CookieGroup::CACHE_TAG]
            );
        } catch (\Exception $e) {
            null;
        }
    }

    /**
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function unsetIsAllowCookies()
    {
        $this->cookieManager->deleteCookie(
            self::ALLOW_COOKIES,
            $this->cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain())
        );
    }

    /**
     * @return string|null
     */
    public function getAllowCookies()
    {
        return $this->cookieManager->getCookie(self::ALLOW_COOKIES);
    }

    public function deleteCookies($cookies)
    {
        try {
            foreach ($cookies as $cookie) {
                if ($this->cookieManager->getCookie($cookie)) {
                    $this->cookieManager->deleteCookie($cookie);
                }
            }
        } catch (\Exception $e) {
            null;
        }
    }
}
