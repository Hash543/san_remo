<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute;

class ListGroup extends \Magento\Framework\View\Element\Html\Select {

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $listGroup;

    /**
     * Magento constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Alfa9\MDirector\Model\Api\ListGroup $listGroup
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Alfa9\MDirector\Model\Api\ListGroup $listGroup,
        array $data = []
    ) {
        $this->listGroup = $listGroup;
        parent::__construct($context, $data);
    }

    /**
     * Get all List
     *
     * @return string
     */
    public function _toHtml(){
        $this->addOption('','-');
        $result = $this->listGroup->fetch();
        if($result && is_array($result) && isset($result['lists']) && is_array($result['lists'])){
            foreach ($result['lists'] as $list) {
                $this->addOption($list['id'], $list['name']);
            }
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

