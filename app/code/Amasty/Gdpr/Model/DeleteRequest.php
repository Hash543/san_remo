<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\Data\DeleteRequestInterface;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest as DeleteRequestResource;
use Magento\Framework\Model\AbstractModel;

class DeleteRequest extends AbstractModel implements DeleteRequestInterface
{
    public function _construct()
    {
        parent::_construct();

        $this->_init(DeleteRequestResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->_getData(DeleteRequestInterface::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(DeleteRequestInterface::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(DeleteRequestInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        $this->setData(DeleteRequestInterface::CUSTOMER_ID, $customerId);

        return $this;
    }
}
