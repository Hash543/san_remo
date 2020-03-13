<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Api;

/**
 * @api
 */
interface CookieRepositoryInterface
{
    /**
     * Save Cookie
     *
     * @param \Amasty\GdprCookie\Api\Data\CookieInterface $cookie
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieInterface
     */
    public function save(\Amasty\GdprCookie\Api\Data\CookieInterface $cookie);

    /**
     * Get cookie by id
     *
     * @param int $cookieId
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($cookieId);

    /**
     * Delete Cookie
     *
     * @param \Amasty\GdprCookie\Api\Data\CookieInterface $cookie
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GdprCookie\Api\Data\CookieInterface $cookie);

    /**
     * Delete cookie by id
     *
     * @param int $cookieId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($cookieId);

    /**
     * Get assigned cookies
     *
     * @param array $usedCookeis
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieInterface[]
     */
    public function getFreeCookies($usedCookeis);
}
