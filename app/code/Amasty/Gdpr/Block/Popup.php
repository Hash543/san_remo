<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Block;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Magento\Framework\View\Element\Template;
use Amasty\Gdpr\Model\Checkbox as CheckboxModel;

class Popup extends Template
{
    protected $_template = 'popup.phtml';

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    /**
     * @var CheckboxModel
     */
    private $checkbox;

    public function __construct(
        CheckboxModel $checkbox,
        Template\Context $context,
        PolicyRepositoryInterface $policyRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->policyRepository = $policyRepository;
        $this->checkbox = $checkbox;
    }

    public function getText()
    {
        $policy = $this->policyRepository->getCurrentPolicy(
            $this->_storeManager->getStore()->getId()
        );

        if ($policy) {
            return $policy->getContent();
        } else {
            return '';
        }
    }

    protected function _toHtml()
    {
        if ($this->isNeedPopup()) {
            return parent::_toHtml();
        }

        return '';
    }

    private function isNeedPopup()
    {
        $listOfBlocks = [
            'amasty_gdpr_newsletter' => CheckboxModel::AREA_NEWSLETTER,
            'customer_form_register' => CheckboxModel::AREA_REGISTRATION,
            'amasty_gdpr_contact' => CheckboxModel::AREA_CONTACT_US,
            'checkout.root' => CheckboxModel::AREA_CHECKOUT,
            'customer_form_register_popup' => CheckboxModel::AREA_REGISTRATION
        ];

        foreach ($listOfBlocks as $block => $area) {
            if ($this->getLayout()->isBlock($block)) {
                if ($this->checkbox->isVisible($area)) {
                    return true;
                }
            }
        }

        return false;
    }
}
