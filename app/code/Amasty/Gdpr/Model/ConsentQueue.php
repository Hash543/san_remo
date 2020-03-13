<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\Data\ConsentQueueInterface;
use Amasty\Gdpr\Model\ResourceModel\ConsentQueue as ConsentQueueResource;
use Magento\Framework\Model\AbstractModel;

class ConsentQueue extends AbstractModel implements ConsentQueueInterface
{
    const STATUS_PENDING = 0;

    const STATUS_SUCCESS = 1;

    const STATUS_FAIL = 2;

    public function _construct()
    {
        parent::_construct();

        $this->_init(ConsentQueueResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(ConsentQueueInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        $this->setData(ConsentQueueInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(ConsentQueueInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(ConsentQueueInterface::STATUS, $status);

        return $this;
    }
}
