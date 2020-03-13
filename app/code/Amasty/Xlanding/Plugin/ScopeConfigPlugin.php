<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Plugin;

use Magento\Framework\Registry;
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;
use Amasty\Xlanding\Cron\CatalogsearchReindex;

class ScopeConfigPlugin
{
    private $coreRegistry;

    public function __construct(
        Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
    }

    public function aroundGetValue(
        Config $config,
        \Closure $closure,
        $path = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {

        /**
         * @TODO We have to understand for what this plugin was added?
         */
//        if (
//            $path == EngineProvider::CONFIG_ENGINE_PATH
//            && $this->coreRegistry->registry(CatalogsearchReindex::REGISTRY_REPLACE_ENGINE)
//        ) {
//            return 'mysql';
//        }

        return $closure($path, $scope, $scopeCode);
    }
}
