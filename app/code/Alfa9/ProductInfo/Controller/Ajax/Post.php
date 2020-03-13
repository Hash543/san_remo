<?php

namespace Alfa9\ProductInfo\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Post extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Alfa9\ProductInfo\Model\Email\ProductInfo
     */
    private $productInfo;

    public function __construct(
        Context $context,
        \Alfa9\ProductInfo\Model\Email\ProductInfo $productInfo
    )
    {
        parent::__construct($context);
        $this->productInfo = $productInfo;
    }

    public function execute()
    {
        $data = [];
        $data['success'] = false;

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $formData = $this->getRequest()->getParams();

        if(is_array($formData)) {
            try {
                $this->productInfo->send($formData['email'], ['data' => new \Magento\Framework\DataObject($formData)]);
                $data['success'] = true;
                $this->messageManager->addSuccessMessage(__('Su consulta ha sido enviado'));
            }catch (\Exception $exception) {
                $data['success'] = false;
                $this->messageManager->addSuccessMessage(__('Su consulta no pudo ser enviada, por favor intente más tarde.'));
            }

        }

        return $resultJson->setData($data);
    }
}