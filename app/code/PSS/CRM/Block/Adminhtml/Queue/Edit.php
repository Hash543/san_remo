<?php
/**
 * @copyright (c) 2018. Alfa9 (http://www.alfa9.com)
 * @author Xavier Sanz <xsanz@alfa9.com>
 * @package pss_crm
 */

namespace PSS\CRM\Block\Adminhtml\Queue;

use Magento\Backend\Block\Widget\Form\Container;


/**
 * Class Edit
 * @package PSS\CRM\Block\Adminhtml\Queue
 */
class Edit extends Container
{
    protected $_coreRegistry = null;

    /**
     * Edit constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'PSS_CRM';
        $this->_controller = 'adminhtml_queue';

        $objId = $this->getRequest()->getParam('id');

        if (!empty($objId)) {
            $this->addButton(
                'delete',
                [
                    'label' => __('Delete'),
                    'class' => 'delete',
                    'onclick' => sprintf(
                        'deleteConfirm("%s", "%s", %s)',
                        __('Are you sure you want to do this? '),
                        $this->getUrl('*/*/delete'),
                        json_encode(['data' => ['id' => $objId]])
                    ),
                ]
            );
            $this->addButton(
                'my_back_button',
                [
                    'label' => __('Back'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('*/*/grid') . '\')',
                    'class' => 'back'
                ],
                -1
            );
        }

        parent::_construct();

        $this->removeButton('back');
        $this->removeButton('save');
        $this->removeButton('reset');

    }

    /**
     * Add Header Text into Form
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('pss_crm_queue_edit_id')) {
            return __("View Record");
        } else {
            return __('New Record');
        }
    }

    /**
     * Permissions
     *
     * @param $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}