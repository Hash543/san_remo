<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Api\Data;

interface DeleteRequestInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const CREATED_AT = 'created_at';
    const CUSTOMER_ID = 'customer_id';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\Gdpr\Api\Data\DeleteRequestInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return \Amasty\Gdpr\Api\Data\DeleteRequestInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     *
     * @return \Amasty\Gdpr\Api\Data\DeleteRequestInterface
     */
    public function setCustomerId($customerId);
}
