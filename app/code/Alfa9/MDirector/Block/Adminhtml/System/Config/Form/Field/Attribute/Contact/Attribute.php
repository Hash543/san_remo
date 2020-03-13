<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Block\Adminhtml\System\Config\Form\Field\Attribute\Contact;

class Attribute extends \Magento\Framework\View\Element\Html\Select {

    /**
     * @var \Alfa9\MDirector\Model\Api\Param
     */
    protected $apiParam;

    /**
     * Magento constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Alfa9\MDirector\Model\Api\Param $apiParam
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Alfa9\MDirector\Model\Api\Param $apiParam,
        array $data = []
    ) {
        $this->apiParam = $apiParam;
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
            'name' => _('First Name'),
            'surname1' => _('Middle Name'),
            'surname2' => _('Last Name'),
            'sex'=> _('Gender'),
            'birthday' => _('Date Of Birth'),
            'reference' => _('Reference'),
            'movil'=> _('Mobile'),
            'city' => _('City'),
            'province' => __('State/Province'),
            'country' => __('Country'),
            'cp' => __('Zip/Postal Code')
        ];
        foreach ($optionsDefault as $key => $option) {
            $this->addOption($key, $option);
        }
        $result = $this->apiParam->fetch();
        if (isset($result['data']) && is_array($result['data'])) {
            foreach ($result['data'] as $param) {
                $this->addOption($param['data'], $param['data']);
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

