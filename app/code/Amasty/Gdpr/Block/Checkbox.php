<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Block;

use Amasty\Gdpr\Model\Checkbox as CheckboxModel;
use Magento\Framework\View\Element\Template;

class Checkbox extends Template implements \Magento\Framework\DataObject\IdentityInterface
{
    protected $_template = 'checkbox.phtml';

    /**
     * @var CheckboxModel
     */
    private $checkboxModel;

    /**
     * @var string
     */
    private $scope;

    public function __construct(
        Template\Context $context,
        CheckboxModel $checkboxModel,
        $scope = CheckboxModel::AREA_REGISTRATION,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->checkboxModel = $checkboxModel;
        $this->scope = $scope;
    }

    /**
     * @return string
     */
    public function getConsentText()
    {
        return $this->checkboxModel->getConsentText();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->checkboxModel->isVisible($this->scope)) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }

    public function getIdentities()
    {
        return [\Amasty\Gdpr\Model\WithConsent::CACHE_TAG];
    }
}
