<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Plugin\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Mageplaza\Shopbybrand\Helper\Data;

/**
 * Class FilterList
 * @package Mageplaza\Shopbybrand\Plugin\Model
 */
class FilterList
{
    /** @var \Mageplaza\Shopbybrand\Helper\Data */
    protected $helper;

    /** @var \Magento\Framework\App\RequestInterface */
    protected $request;

    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    /**
     * FilterList constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param RequestInterface $request
     * @param Data $helper
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        RequestInterface $request,
        Data $helper
    ) {
        $this->objectManager = $objectManager;
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\FilterList $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterGetFilters(\Magento\Catalog\Model\Layer\FilterList $subject, $result)
    {
        if ($this->request->getFullActionName() != 'mpbrand_index_view') {
            return $result;
        }

        $brandAttCode = $this->helper->getAttributeCode();
        foreach ($result as $key => $filter) {
            if ($filter->getRequestVar() == $brandAttCode) {
                $filterBrand = $this->objectManager->create(
                    'Mageplaza\Shopbybrand\Model\Layer\Filter\Attribute',
                    ['data' => ['attribute_model' => $filter->getAttributeModel()], 'layer' => $filter->getLayer()]
                );
                $result[$key] = $filterBrand;
                break;
            }
        }

        return $result;
    }
}
