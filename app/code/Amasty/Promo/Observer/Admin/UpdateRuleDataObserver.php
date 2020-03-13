<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Observer\Admin;

use Magento\Framework\Event\ObserverInterface;

class UpdateRuleDataObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /** @var \Magento\Framework\Stdlib\StringUtils */
    protected $_string;
    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializerBase;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Amasty\Base\Model\Serializer $serializerBase
    ) {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry  = $registry;
        $this->_string        = $string;
        $this->serializerBase = $serializerBase;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $salesrule = $this->_coreRegistry->registry('ampromo_current_salesrule');

        if (!$salesrule) {
            return;
        }

        $ampromoData = $observer->getRequest()->getParam('ampromorule');

        foreach ($observer->getRequest()->getParams() as $key => $value) {
            if ($this->_string->strpos($key, 'ampromorule_') !== false) {
                $ampromoData[str_replace('ampromorule_', '', $key)] = is_array($value)
                    ? $this->serializerBase->serialize($value)
                    : $value;
            }
        }

        if (!$observer->getRequest()->getParam('ampromorule_top_banner_image')) {
            $ampromoData['top_banner_image'] = null;
        }

        if (!$observer->getRequest()->getParam('ampromorule_after_product_banner_image')) {
            $ampromoData['after_product_banner_image'] = null;
        }

        if (!$observer->getRequest()->getParam('ampromorule_after_product_banner')) {
            $ampromoData['after_product_banner'] = null;
        }

        if (!$observer->getRequest()->getParam('ampromorule_label_image')) {
            $ampromoData['label_image'] = null;
        }

        if ($salesrule->getId() && $ampromoData) {
            $ampromoRule = $this->_objectManager->create('Amasty\Promo\Model\Rule');

            $ampromoRule->load($salesrule->getId(), 'salesrule_id')
                ->addData($ampromoData)
                ->setData('salesrule_id', $salesrule->getId())
                ->save();
        }

        if (in_array($salesrule->getSimpleAction(), \Amasty\Promo\Observer\Salesrule\Discount::PROMO_RULES)) {
            $salesrule->setData(\Magento\SalesRule\Model\Data\Rule::KEY_SIMPLE_FREE_SHIPPING, 0)->save();
        }
    }
}