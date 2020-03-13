<?php

namespace Alfa9\Treatment\Controller\Treatment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class AddProduct extends Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * AddProduct constructor.
     * @param Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        parent::__construct($context);
        $this->formKey = $formKey;
        $this->productFactory = $productFactory;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $encryptSku = $this->getRequest()->getParam('k', null);
        if ($encryptSku === null) {
            return $resultRedirect->setPath('/');
        }

        $decryptSku = base64_decode($encryptSku);
        $product = $this->productFactory->create();
        $productId = $product->getIDBySku($decryptSku);
        if ($productId) {
            $product->load($product->getIdBySku($decryptSku));

            $productId = $product->getId();
            $params = [
                'form_key' => $this->formKey->getFormKey(),
                'product' => $productId,
                'qty' => 1,
            ];
            return $resultRedirect->setPath('checkout/cart/add', $params);
        }
        return $resultRedirect->setPath('/');
    }
}
