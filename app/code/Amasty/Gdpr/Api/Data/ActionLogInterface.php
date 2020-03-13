<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Api\Data;

interface ActionLogInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const CREATED_AT = 'created_at';
    const IP = 'ip';
    const ACTION = 'action';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\Gdpr\Api\Data\ActionLogInterface
     */
    public function setId($id);

    /**
     * @return int|null
     */
    public function getCustomerId();

    /**
     * @param int|null $customerId
     *
     * @return \Amasty\Gdpr\Api\Data\ActionLogInterface
     */
    public function setCustomerId($customerId);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return \Amasty\Gdpr\Api\Data\ActionLogInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getIp();

    /**
     * @param string $ip
     *
     * @return \Amasty\Gdpr\Api\Data\ActionLogInterface
     */
    public function setIp($ip);

    /**
     * @return string
     */
    public function getAction();

    /**
     * @param string $action
     *
     * @return \Amasty\Gdpr\Api\Data\ActionLogInterface
     */
    public function setAction($action);
}
