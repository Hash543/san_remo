<?php

namespace PSS\DeleteAccount\Block\Customer\Account;

use Magento\Framework\View\Element\Template;

class Delete extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * Delete constructor.
     * @param Template\Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
        $this->session = $session;
    }

    public function getCustomerId()
    {
        return $this->session->getCustomer()->getId();
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete');
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}