<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\ConfigurableProduct\Helper\ProductAlert;

use Magento\Store\Model\Store;

/**
 * ProductAlert data helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 *
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\ProductAlert\Helper\Data {
    /**
     * @param string $type
     * @param string $url
     * @return string
     */
    public function getSaveUrlAjax($type, $url)
    {

        return $this->_getUrl(
            'productalert/add/' . $type,
            [
                'product_id' => $this->getProduct()->getId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl($url)
            ]
        );
    }
}