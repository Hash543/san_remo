<?php

namespace Alfa9\StoreInfo\Model\Stores;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Alfa9\StoreInfo\Model\Stores;

class Url
{
    /**
     * @var string
     */
    const URL_CONFIG_PATH      = 'storeinfo/stockist_content/url';

    /**
     * url builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    public $urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getListUrl()
    {
        $sefUrl = $this->scopeConfig->getValue(self::URL_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
        if ($sefUrl) {
            return $this->urlBuilder->getUrl('', ['_direct' => $sefUrl]);
        }
        return $this->urlBuilder->getUrl('storeinfo/stores/index');
    }

    /**
     * @param Stores $stockist
     * @return string
     */
    public function getStockistUrl(Stores $stockist)
    {
        if ($urlKey = $stockist->getUrlKey()) {
            return $this->urlBuilder->getUrl('', ['_direct'=>$urlKey]);
        }
        return $this->urlBuilder->getUrl('storeinfo/stores/view', ['id' => $stockist->getId()]);
    }
}
