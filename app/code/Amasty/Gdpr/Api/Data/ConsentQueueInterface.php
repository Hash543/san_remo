<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Api\Data;

interface ConsentQueueInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const STATUS = 'status';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\Gdpr\Api\Data\ConsentQueueInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     *
     * @return \Amasty\Gdpr\Api\Data\ConsentQueueInterface
     */
    public function setCustomerId($customerId);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\Gdpr\Api\Data\ConsentQueueInterface
     */
    public function setStatus($status);
}
