<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Rule\Plugin\Model\Rule;

use Magento\Framework\Exception\NoSuchEntityException;

class DataProvider {
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * DataProvider constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule\DataProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetData(\Magento\SalesRule\Model\Rule\DataProvider $subject, $result) {
        if($result) {
            foreach ($result as $index => $data) {
                if(isset($data['banner'])) {
                    $result[$index]['banner'] = [];
                    $result[$index]['banner'][] = [
                        'name' => $data['banner'],
                        'type' => 'image',
                        'url' => $this->getMediaUrl($data['banner'])
                    ];
                }
            }
        }
        return $result;
    }

    /**
     * @param string $banner
     * @return string
     */
    public function getMediaUrl($banner) {
        try {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->storeManager->getStore();
            $mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        } catch (NoSuchEntityException $exception) {
            $mediaUrl = '';
        }
        $mediaUrl = $mediaUrl.'promotion/tmp/banner/'.$banner;
        return $mediaUrl;
    }
}