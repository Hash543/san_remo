<?php

namespace Pss\Redsys\Helper;

use Magento\Framework\App\Helper\Context;

class Validator extends \Codeko\Redsys\Helper\Validator
{

    /**
     * @param $pedido
     * @return false|int
     */
    private function checkPedidoNum($pedido)
    {
        return preg_match("/^\w{1,12}$/", $pedido);
    }

}
