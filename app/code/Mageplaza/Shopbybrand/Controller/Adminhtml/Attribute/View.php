<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Controller\Adminhtml\Attribute;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Mageplaza\Shopbybrand\Helper\Data;

/**
 * Class View
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Attribute
 */
class View extends Action
{
    /**
     * @type \Mageplaza\Shopbybrand\Helper\Data
     */
    protected $_brandHelper;

    /**
     * @type \Magento\Catalog\Model\Product\Attribute\Repository
     */
    protected $_productAttributeRepository;

    /**
     * View constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Model\Product\Attribute\Repository $productRespository
     * @param \Mageplaza\Shopbybrand\Helper\Data $brandHelper
     */
    public function __construct(
        Context $context,
        Repository $productRespository,
        Data $brandHelper
    ) {
        $this->_brandHelper = $brandHelper;
        $this->_productAttributeRepository = $productRespository;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $attributeCode = $this->_brandHelper->getAttributeCode();
        try {
            $attribute = $this->_productAttributeRepository->get($attributeCode);

            $this->_forward('edit', 'product_attribute', 'catalog', ['attribute_id' => $attribute->getId()]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('You have to choose an attribute as brand in configuration.'));
            $this->_redirect('adminhtml/system_config/edit', ['section' => 'shopbybrand']);
        }
    }
}
