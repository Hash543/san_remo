<?php
/**
 * @author Israel Yasis
 */
namespace PSS\ProductAlert\Model;

/**
 * Class UrlHash
 * @package PSS\ProductAlert\Model
 */
class UrlHash
{
    const SALT = 'pssdigital';

    public function getHash($productId, $email) {
        return md5($productId . $email . self::SALT);
    }

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @return bool
     */
    public function check(\Magento\Framework\App\Request\Http $request)
    {
        $hash = $request->getParam('hash');
        $productId = $request->getParam('product_id');
        $email = urldecode($request->getParam('email'));

        if (empty($hash) || empty($productId) || empty($email)) {
            return false;
        }

        $real = $this->getHash($productId, $email);

        return $hash == $real;
    }
}
