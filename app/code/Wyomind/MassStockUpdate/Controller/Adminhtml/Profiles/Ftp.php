<?php
/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

class Ftp extends \Magento\Backend\App\Action
{

    protected $_ftpHelper;

    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Wyomind\MassStockUpdate\Helper\Ftp $ftpHelper
    )
    {

        parent::__construct($context);
        $this->_ftpHelper = $ftpHelper;
    }

    /**
     *
     */
    public function execute()
    {

        try {
            $data = $this->getRequest()->getParams();
            $ftp = $this->_ftpHelper->getConnection($data);

            $content = __("Connection succeeded");
            $ftp->close();
        } catch (\Exception $e) {
            $content = $e->getMessage();
        }

        $this->getResponse()->representJson($this->_objectManager->create('Magento\Framework\Json\Helper\Data')->jsonEncode($content));
    }

}
