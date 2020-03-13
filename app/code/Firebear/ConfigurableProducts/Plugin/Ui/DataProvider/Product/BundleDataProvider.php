<?php

namespace Firebear\ConfigurableProducts\Plugin\Ui\DataProvider\Product;

use Firebear\ConfigurableProducts\Helper\Data as IcpHelper;

class BundleDataProvider
{
    private $icpHelper;

    public function __construct(IcpHelper $icpHelper)
    {
        $this->icpHelper = $icpHelper;
    }

    public function aroundGetData(
        \Magento\Bundle\Ui\DataProvider\Product\BundleDataProvider $subject,
        callable $proceed
    ) {
        if ($this->icpHelper->getGeneralConfig('bundle_options/enable')) {
            if (!$subject->getCollection()->isLoaded()) {
                $subject->getCollection()->addAttributeToFilter(
                    'type_id',
                    [
                        'simple'       => 'simple',
                        'virtual'      => 'virtual',
                        'configurable' => 'configurable'
                    ]
                );
                //$subject->getCollection()->addFilterByRequiredOptions();
                $subject->getCollection()->addStoreFilter(
                    \Magento\Store\Model\Store::DEFAULT_STORE_ID
                );
                $subject->getCollection()->load();
            }
            $items = $subject->getCollection()->toArray();

            return [
                'totalRecords' => $subject->getCollection()->getSize(),
                'items'        => array_values($items),
            ];
        } else {
            return $proceed();
        }
    }
}