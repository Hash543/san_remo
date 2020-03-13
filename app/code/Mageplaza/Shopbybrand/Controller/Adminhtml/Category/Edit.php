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

namespace Mageplaza\Shopbybrand\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Shopbybrand\Controller\Adminhtml\Category;
use Mageplaza\Shopbybrand\Helper\Data as HelperData;
use Mageplaza\Shopbybrand\Model\CategoryFactory;

/**
 * Class Edit
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Category
 */
class Edit extends Category
{
    /**
     * @var \Mageplaza\Shopbybrand\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Mageplaza\Shopbybrand\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Edit constructor.
     *
     * @param \Mageplaza\Shopbybrand\Helper\Data $data
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Mageplaza\Shopbybrand\Model\CategoryFactory $categoryFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        HelperData $data,
        Data $jsonHelper,
        PageFactory $pageFactory,
        Registry $registry,
        CategoryFactory $categoryFactory,
        Context $context
    ) {
        $this->helper = $data;
        $this->jsonHelper = $jsonHelper;
        $this->registry = $registry;
        $this->resultPageFactory = $pageFactory;
        $this->categoryFactory = $categoryFactory;

        parent::__construct($context);
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $cat = $this->categoryFactory->create();
        if ($id = $this->getRequest()->getParam('cat_id')) {
            $cat->load($id);
            if (!$cat->getId()) {
                $this->messageManager->addErrorMessage(__('The category does not exist.'));
                $this->_redirect('*/*/');

                return;
            }
        }

        //Set entered data if was error when we do save
        $data = $this->_session->getProductFormData(true);
        if (!empty($data)) {
            $cat->setData($data);
        }

        $this->registry->register('current_brand_category', $cat);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($cat->getId() ? $cat->getName() : __('New Category'));

        return $resultPage;
    }
}
