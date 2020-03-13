<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute;

class GroupCustomer extends \Magento\Framework\View\Element\Html\Select {

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $groupFactory;

    /**
     * Magento constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        array $data = []
    ) {
        $this->groupFactory = $groupFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get all Customer Groups
     *
     * @return string
     */
    public function _toHtml(){
        $groups = $this->groupFactory->create()->getCollection();
        $this->addOption('','-');
        /** @var \Magento\Customer\Model\Group $group */
        foreach ($groups as $group){
            $this->addOption($group->getId(), $group->getCode());
        }

        return parent::_toHtml();
    }

    /**
     * Set select name
     *
     * @param $value
     * @return mixed
     * @throws \Exception
     */
    public function setInputName($value) {
        return $this->setName($value);
    }

}

