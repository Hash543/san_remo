<?php
/**
 * @copyright (c) 2018. Alfa9 (http://www.alfa9.com)
 * @author Xavier Sanz <xsanz@alfa9.com>
 * @package iese_publishing
 */

namespace PSS\CRM\Block\Adminhtml\Queue\Edit;

use \Magento\Backend\Block\Widget\Form\Generic;

/**
 * Class Form
 * @package PSS\CRM\Block\Adminhtml\Queue\Edit
 */
class Form extends Generic
{
    protected $_systemStore;

    protected $registry;

    protected $queueRepositoryInterface;

    protected $context;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \PSS\CRM\Api\QueueRepositoryInterface $queueRepositoryInterface,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->queueRepositoryInterface = $queueRepositoryInterface;
        $this->context = $context;
        $this->registry = $registry;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Get parent construct and set title and id
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('queue_form');
        $this->setTitle(__('View Queue Record'));
    }

    /**
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    protected function _prepareForm()
    {
        $queueItem = $this->queueRepositoryInterface->getById($this->registry->registry('pss_crm_queue_edit_id'));

        $form = $this->_formFactory->create(
            [
                'data' =>
                    [
                        'id' => 'edit_form',
                        'action' => $this->getData('action'),
                        'method' => 'post',
                        'enctype' => 'multipart/form-data'
                    ]
            ]
        );

        $form->setHtmlIdPrefix('queue_options_form_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('View Queue Record'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'pss_crm_queue_id',
            'hidden',
            [
                'name' => 'pss_crm_queue_id',
            ]
        );

        $fieldset->addField(
            'model',
            'hidden',
            [
                'name' => 'model',
            ]
        );


        $fieldset->addField(
            'process_name',
            'text',
            [
                'name' => 'process_name',
                'label' => __('Process Name'),
                'title' => __('Process Name'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'method',
            'text',
            [
                'name' => 'method',
                'label' => __('Method'),
                'title' => __('Method'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'data',
            'textarea',
            [
                'name' => 'data',
                'label' => __('Data'),
                'title' => __('Data'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'process_message',
            'textarea',
            [
                'name' => 'process_message',
                'label' => __('Request'),
                'title' => __('Request'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'result',
            'textarea',
            [
                'name' => 'result',
                'label' => __('Response'),
                'title' => __('Response'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'created_at',
            'text',
            [
                'name' => 'created_at',
                'label' => __('Created At'),
                'title' => __('Created At'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'executed_at',
            'text',
            [
                'name' => 'executed_at',
                'label' => __('Last Executed At'),
                'title' => __('Last Executed At'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'process_status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'process_status',
                'required' => true,
                'values' => array(
                    array('value'=>'0','label' => __('Pending')),
                    array('value'=>'1','label' => __('Send')),
                    array('value'=>'2','label' => __('Error')),
                ),
            ]
        );

        $form->setUseContainer(true);

        if ($queueItem) {
            $form->setValues($queueItem->getData());
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}