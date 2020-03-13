<?php
/**
 *  @author Xavier Sanz <xsanz@pss.com>
 *  @copyright Copyright (c) 2017 PSS (http://www.pss.com)
 *  @package PSS
 */

namespace PSS\CRM\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{



    public function getConfig($config = null)
    {
        if (!$config) {
            return null;
        }

        return $this->scopeConfig->getValue($config);
    }

    public function encryptPassword($password = null)
    {
        if(!$password) {
            return null;
        }

        //https://stackoverflow.com/questions/18619607/php-mcrypt-rijndael-128-encryption-in-c-sharp

        $key = implode(array_map("chr", [109, 104, 108, 243, 122, 85, 34, 232, 164, 78, 3, 152, 59, 190, 177, 170, 65, 152, 99, 52, 193, 51, 131, 14, 223, 194, 82, 220, 127, 188, 24, 202]));
        $iv = implode(array_map("chr", [150, 209, 58, 5, 161, 73, 103, 61, 54, 119, 90, 177, 139, 99, 152, 236]));

        //TODO: THIS METHOD WILL BE DEPRECATED ON PHP 7.1
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);

        $padding = $block - (strlen($password) % $block);
        $password .= str_repeat(chr($padding), $padding);

        //TODO: THIS METHOD WILL BE DEPRECATED ON PHP 7.1
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $password, MCRYPT_MODE_CBC, $iv));

    }
}