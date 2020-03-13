<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * This package designed for Magento COMMUNITY edition
 * PSS Digital does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PSS Digital does not provide extension support in case of * incorrect edition usage.
 *
 * @author PSS Digital Team
 * @category PSS
 * @package PSS_WordPress
 * @copyright Copyright (c) 2018 PSS (https://www.pss-ti.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace PSS\WordPress\Controller\Adminhtml\WordPress;

class PostCmsPage extends PostGrid {
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $cmsPageId = $this->getRequest()->getParam('page_id');
        $model = $this->_objectManager->create('Magento\Cms\Model\Page');
        if ($cmsPageId) {
            $model->load($cmsPageId);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This CMS Page no longer exists.'));
                $this->_redirect('adminhtml/*');
                return;
            }
        }
        if (!$this->registry->registry('cms_page')) {
            $this->registry->register('cms_page', $model);
        }
        parent::execute();
    }
}