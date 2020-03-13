<?php

namespace Alfa9\Treatment\Model;

use Magento\Framework\Model\AbstractModel;

class Treatment extends AbstractModel
{
    /**
     * @var \Alfa9\Treatment\Helper\Email
     */
    private $email;


    public function _construct()
    {
        $this->_init(\Alfa9\Treatment\Model\ResourceModel\Treatment::class);
    }
}