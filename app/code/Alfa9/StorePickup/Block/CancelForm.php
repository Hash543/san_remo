<?php
namespace Alfa9\StorePickup\Block;

use Magento\Framework\View\Element\Template;

class CancelForm extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * CancelForm constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    /**
     * Returns action url for contact form
     *
     * @return string
     */
    public function getFormAction()
    {
        $params = $this->getRequest()->getParams();
        $params['_secure'] = true;
        return $this->getUrl('pickup/cancel/post', $params);
    }

    public function getOrderId() {
        return $this->registry->registry('order_id');
    }
}
