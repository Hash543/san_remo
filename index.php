<?php
/**
 * Application entry point
 *
 * Example - run a particular store or website:
 * --------------------------------------------
 * require __DIR__ . '/app/bootstrap.php';
 * $params = $_SERVER;
 * $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'website2';
 * $params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'website';
 * $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
 * \/** @var \Magento\Framework\App\Http $app *\/
 * $app = $bootstrap->createApplication(\Magento\Framework\App\Http::class);
 * $bootstrap->run($app);
 * --------------------------------------------
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use PSS\Loyalty\Helper\Data as LoyaltyHelper;

try {
    require __DIR__ . '/app/bootstrap.php';
} catch (\Exception $e) {
    echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Autoload error</h3>
    </div>
    <p>{$e->getMessage()}</p>
</div>
HTML;
    exit(1);
}

/* Store or website code */
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : 'base';

/* Run store or run website */
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'website';
/*
if(strpos($_SERVER['REQUEST_URI'], LoyaltyHelper::WEBSITE_CODE) ||
    (isset($_SERVER['HTTP_REFERER'])
        && ((strpos($_SERVER['HTTP_REFERER'], LoyaltyHelper::WEBSITE_CODE) && strpos($_SERVER['REQUEST_URI'], 'cart')) ||
            (strpos($_SERVER['HTTP_REFERER'], LoyaltyHelper::WEBSITE_CODE) && strpos($_SERVER['REQUEST_URI'], 'product/view'))
        ))
    || (isset($_GET['iberia']) && strpos($_SERVER['REQUEST_URI'], 'onepage'))
    || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'iberia=1') && strpos($_SERVER['REQUEST_URI'], 'onepage'))
    || (strpos($_SERVER['REQUEST_URI'], 'iberia=1'))) {
    $mageRunCode = LoyaltyHelper::WEBSITE_CODE;
}*/
/*$requestUri = $_SERVER['REQUEST_URI'];
if(strpos($requestUri, '/fidelizacion') !== false) {
	$mageRunCode = LoyaltyHelper::WEBSITE_CODE;	
}*/
//echo $mageRunCode;
//die();
$params = $_SERVER;
$params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = $mageRunCode;
$params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = $mageRunType;

$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
/** @var \Magento\Framework\App\Http $app */
$app = $bootstrap->createApplication(\Magento\Framework\App\Http::class);
$bootstrap->run($app);
