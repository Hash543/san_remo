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


abstract class PostGrid extends \Magento\Backend\App\Action
{
    protected $_publicActions = ['postgrid'];

    const ADMIN_RESOURCE = 'PSS_WordPress::post_grid';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * PostGrid constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry )
    {
        parent::__construct($context);
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock(
            'post_grid'
        )->setSelectedPost(
            $this->getRequest()->getPost('selected_post')
        );
        $this->_view->renderLayout();
    }
    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
