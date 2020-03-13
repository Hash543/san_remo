<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * This package designed for Magento COMMUNITY edition
 * PSS Digital does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PSS Digital does not provide extension support in case of * incorrect edition usage.
 *
 * @author PSS Digital Team
 * @category PSS
 * @package PSS_WordPress
 * @copyright Copyright (c) 2018 PSS (https://www.pss-ti.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace PSS\WordPress\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\Dir as ModuleDir;
use PSS\WordPress\Model\Factory;

class Core extends AbstractHelper
{
    /**
     * @var
     */
    protected $helper;
    /**
     * @var FullModuleList
     */
    protected $fullModuleList;
    /**
     * @var ModuleDir
     */
    protected $moduleDir;
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * Core constructor.
     * @param Context $context
     * @param FullModuleList $fullModuleList
     * @param ModuleDir $moduleDir
     * @param Factory $factory
     */
    public function __construct(Context $context, FullModuleList $fullModuleList, ModuleDir $moduleDir, Factory $factory)
    {
        $this->fullModuleList = $fullModuleList;
        $this->moduleDir = $moduleDir;
        $this->factory = $factory;
        parent::__construct($context);
    }

    /**
     * @return bool|mixed
     */
    public function getHelper()
    {
        if ($this->helper !== null) {
            return $this->helper;
        }

        $this->helper = false;

        foreach ($this->fullModuleList->getNames() as $moduleName) {
            if (strpos($moduleName, 'PSS_WordPress_') !== 0) {
                continue;
            }

            $coreHelperFile = dirname($this->moduleDir->getDir($moduleName, ModuleDir::MODULE_ETC_DIR)) . '/Helper/Core.php';

            if (is_file($coreHelperFile)) {
                if ($coreHelper = $this->factory->get('WordPress\\' . str_replace('WordPress_', '', $moduleName) . '\\Helper\\Core\\Proxy')) {
                    $this->helper = $coreHelper;
                    break;
                }
            }
        }

        return $this->helper;
    }
}
