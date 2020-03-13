<?php
/**
 * Created by PhpStorm.
 * User: xavier.sanz
 * Date: 14/11/18
 * Time: 16:11
 */

namespace Alfa9\StockExpress\Api;


interface StockRepositoryInterface
{

    /**
     * Returns stores by product id
     *
     * @api
     * @param string $sku
     * @param int $quantity
     * @return string[]
     */
    public function getList($sku, $quantity);

    /**
     * @api
     * Returns stores by multiple products
     * @param array $products
     * @return string[]
     */
    public function getListMulti($products);
}