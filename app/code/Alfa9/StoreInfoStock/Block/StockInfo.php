<?php

namespace Alfa9\StoreInfoStock\Block;


class StockInfo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Alfa9_StoreInfoStock::ajax/stockinfo.phtml';
    /**
     * @var \Alfa9\StoreInfo\Api\Data\StockistInterface[]
     */
    protected $storeList = [];

    /**
     * StockInfo constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Alfa9\StoreInfoStock\Helper\StockInfoApi $stockInfoApi
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Alfa9\StoreInfoStock\Helper\StockInfoApi $stockInfoApi,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param \Alfa9\StoreInfo\Api\Data\StockistInterface[] $storeList
     */
    public function setStores($storeList)
    {
        $this->storeList = $storeList;
    }

    /**
     * @return array
     */
    public function getStoreList()
    {
        $stores = [];
        /** @var \Alfa9\StoreInfo\Api\Data\StockistInterface $store */
        foreach ($this->storeList as $store) {
            $url = null;
            if ($store->getLink()) {
                $url = $this->getUrl('storeinfo/view/index');
                $url = $url . $store->getLink();
            }
            $stores[] = [
                'address' => $store->getAddress(),
                'city' => $store->getCity(),
                'schedule' => $store->getSchedule(),
                'delivery' => $store->getAvailability() ? $store->getAvailability(): '72Hrs' ,
                'link' => $url,
                'name' => $store->getName()
            ];
        }
        return $stores;
    }
}