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

namespace PSS\WordPress\Model;

/* Constructor Args */
use PSS\WordPress\Model\OptionManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;
use PSS\WordPress\Model\DirectoryList;

/* Misc */

use PSS\WordPress\Model\Integration\IntegrationException;

class Theme
{
    /*
     *
     * @var
     *
     */
    const THEME_NAME = 'pss';

    /*
     *
     * @var
     *
     */
    protected $optionManager;

    /*
     *
     * @var
     *
     */
    protected $scopeConfig;

    /*
     *
     * @var
     *
     */
    protected $storeManager;

    /*
     *
     * @var
     *
     */
    protected $wpDirectoryList;

    /*
     *
     *
     *
     */
    public function __construct(
        OptionManager $optionManager,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        State $state,
        DirectoryList $wpDirectoryList
    ) {
        $this->optionManager = $optionManager;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->wpDirectoryList = $wpDirectoryList;
    }

    /*
     *
     *
     *
     */
    public function validate()
    {
        if ($this->state->getAreaCode() !== 'adminhtml') {
            return $this;
        }

        if (!$this->wpDirectoryList->isValidBasePath()) {
            IntegrationException::throwException('Empty or invalid path set.');
        }

        $targetDir = $this->getTargetDir();
        $sourceDir = $this->getModuleDir() . '/wptheme';

        $sourceCssFile = $sourceDir . '/style.css';
        $targetCssFile = $targetDir . '/style.css';

        if (!is_dir($targetDir) || !is_file($targetCssFile) || md5_file($sourceCssFile) !== md5_file($targetCssFile)) {
            // Either theme not installed or version changes
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0777, true);

                if (!is_dir($targetDir)) {
                    IntegrationException::throwException(
                        'The WordPress theme is not installed and due to the permissions of the WordPress theme folder, it cannot be installed automatically. Please copy the contents of app/code/PSS/WordPress/wptheme to the wp-content/themes/pss folder.'
                    );
                }
            }

            // Get source files. Loop through and copy to WordPress
            $sourceFiles = scandir($sourceDir);

            foreach ($sourceFiles as $sourceFile) {
                if (trim($sourceFile, '.') === '') {
                    continue;
                }

                $targetFile = $targetDir . '/' . $sourceFile;
                $sourceFile = $sourceDir . '/' . $sourceFile;

                if (!$this->isFileWriteable($targetFile)) {
                    IntegrationException::throwException('Unable to install a WordPress theme file due to permissions. File is ' . $targetFile);
                }

                $sourceData = file_get_contents($sourceFile);
                $targetData = file_exists($targetFile) ? file_get_contents($targetFile) : '';

                if ($sourceData !== $targetData) {
                    file_put_contents($targetFile, $sourceData);
                }
            }
        }

        if (!$this->isActive()) {
            IntegrationException::throwException(
                'The WordPress theme is installed but is not active. Please login to the WordPress Admin and enable it.'
            );
        }

        return $this;
    }

    /*
     *
     *
     *
     */
    public function isFileWriteable($file)
    {
        return is_file($file) && is_writeable($file) || !is_file($file) && is_writable(dirname($file));
    }

    /*
     *
     *
     *
     */
    public function isActive()
    {
        return $this->optionManager->getOption('template') === self::THEME_NAME && $this->optionManager->getOption('stylesheet') === self::THEME_NAME;
    }

    /*
     *
     *
     *
     */
    public function getTargetDir()
    {
        return $this->wpDirectoryList->getThemeDir() . '/' . self::THEME_NAME;
    }

    /*
     *
     *
     *
     */
    public function getSourceDir()
    {
        return $this->getModuleDir() . '/wptheme';
    }

    /*
     *
     *
     *
     */
    public function canAutoInstallTheme()
    {
        return (int)$this->_request->getParam('install-theme') === 1;
    }

    /*
     *
     *
     * @return bool
     */
    public function isThemeIntegrated()
    {
        return (int)$this->scopeConfig->getValue(
                'wordpress/setup/theme_integration',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                (int)$this->storeManager->getStore()->getId()
            ) === 1;
    }

    /*
     *
     * @return string
     */
    protected function getModuleDir()
    {
        return dirname(__DIR__);
    }
}
