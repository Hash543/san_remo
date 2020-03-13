<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute\Customer;

class Attribute extends \Magento\Framework\View\Element\Html\Select {

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $attributeCollection;

    /**
     * Magento constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        array $data = []
    ) {
        $this->attributeCollection = $attributeCollection;
        parent::__construct($context, $data);
    }

    /**
     * Get all List
     *
     * @return string
     */
    public function _toHtml(){
        $this->addOption('','-- Please select --');
        $optionsDefault = [
            'company' => _('Company'),
            'street' => _('Street'),
            'city' => _('City'),
            'region'=> _('State/Province'),
            'country' => _('Country'),
            'postcode' => _('Zip/Postal Code'),
            'telephone'=> _('Telephone'),
            'fax' => _('Fax')
        ];

        foreach ($optionsDefault as $key => $option) {
            $this->addOption($key, $option);
        }
        /**
         * @var \Magento\Customer\Model\ResourceModel\Attribute\Collection $attributeCollection
         */
        $attributeCollection = $this->attributeCollection->create();
        $collection = $attributeCollection
            ->addVisibleFilter()
            ->addExcludeHiddenFrontendFilter()
            ->addFieldToFilter('attribute_code', array('neq' => 'email'));
        /**
         * @var \Magento\Customer\Model\Attribute $attribute
         */
        foreach ($collection as $attribute) {
            $this->_options[] = array(
                'value' => $attribute->getAttributeCode(),
                'label' => __($attribute->getFrontendLabel())
            );
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

