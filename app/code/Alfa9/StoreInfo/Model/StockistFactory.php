<?php

namespace Alfa9\StoreInfo\Model;

use Magento\Framework\ObjectManagerInterface;
use Alfa9\StoreInfo\Model\Routing\RoutableInterface;

class StockistFactory implements FactoryInterface
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    public $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    public $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(ObjectManagerInterface $objectManager, $instanceName = Stores::class)
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName  = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return RoutableInterface|\Alfa9\StoreInfo\Model\Stores
     */
    public function create(array $data = array())
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
