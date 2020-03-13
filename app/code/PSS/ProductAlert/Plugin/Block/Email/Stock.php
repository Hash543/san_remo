<?php
/**
 * @author Israel Yasis
 */
namespace PSS\ProductAlert\Plugin\Block\Email;
/**
 * Class Stock
 * @package PSS\ProductAlert\Plugin\Block\Email
 */
class Stock {
    /**
     * @var
     */
    private $data;

    /**
     * @var int
     */
    private $productId;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \PSS\ProductAlert\Model\UrlHash
     */
    private $urlHash;

    /**
     * Stock constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \PSS\ProductAlert\Model\UrlHash $urlHash
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \PSS\ProductAlert\Model\UrlHash $urlHash
    ) {
        $this->registry = $registry;
        $this->urlHash = $urlHash;

    }

    /**
     * @param $subject
     * @return null|string
     */
    protected function getType($subject)
    {
        $type = null;
        if ($subject instanceof \Magento\ProductAlert\Block\Email\Price) {
            $type = 'price';
        }
        if ($subject instanceof \Magento\ProductAlert\Block\Email\Stock) {
            $type = 'stock';
        }

        return $type;
    }

    /**
     * @param $subject
     * @param string $route
     * @param array $params
     * @return array
     */
    public function beforeGetUrl($subject, $route = '', $params = [])
    {
        $registryData = $this->registry->registry('pss_notify_data');
        if (isset($registryData['guest']) && $registryData['guest'] && isset($registryData['email'])) {
            if ($type = $this->getType($subject)) {
                $hash = $this->urlHash->getHash(
                    $this->productId,
                    $registryData['email']
                );
                $params['product_id'] = $this->getProductId();
                $params['email'] = urlencode($registryData['email']);
                $params['hash'] = urlencode($hash);
                $params['type'] = $type;
            }
        }

        return [$route, $params];
    }

    /**
     * @param $subject
     * @param $productId
     */
    public function beforeGetProductUnsubscribeUrl($subject, $productId)
    {
        $this->setProductId($productId);
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}