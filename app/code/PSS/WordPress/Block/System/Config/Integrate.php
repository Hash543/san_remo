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
namespace PSS\WordPress\Block\System\Config;

/* Parent Class */
use Magento\Backend\Block\Template;

/* Constructor Args */
use Magento\Backend\Block\Template\Context;
use PSS\WordPress\Model\IntegrationManager;
use PSS\WordPress\Model\Url;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\Module\Manager as ModuleManager;
use PSS\WordPress\Model\Plugin;
use Magento\Framework\Module\ResourceInterface;

class Integrate extends Template
{
	const YOAST_SEO_PLUGIN_URL = 'https://wordpress.org/plugins/wordpress-seo/';
	
	const YOAST_SEO_MODULE_URL = 'https://github.com/bentideswell/magento2-wordpress-integration-yoastseo';

    /**
     * @var IntegrationManager
     */
	protected $integrationManager;

    /**
     * @var Url
     */
	protected $url;

    /**
     * @var StoreManager
     */
	protected $storeManager;

    /**
     * @var Emulation
     */
	protected $emulator;

    /**
     * @var Plugin
     */
	protected $plugin;

    /**
     * @var ModuleManager
     */
	protected $moduleManager;

    /**
     * @var ResourceInterface
     */
	protected $resourceInterface;

    /**
     * @var \Exception
     */
	protected $exception;
    /**
     * @var bool|string
     */
	protected $success = false;

    /**
     * Integrate constructor.
     * @param Context $context
     * @param IntegrationManager $integrationManager
     * @param Url $url
     * @param StoreManager $storeManager
     * @param Emulation $emulator
     * @param ModuleManager $moduleManager
     * @param Plugin $plugin
     * @param ResourceInterface $resourceInterface
     * @param array $data
     */
  public function __construct(
  	Context $context,
  	IntegrationManager $integrationManager,
  	Url $url,
    StoreManager $storeManager,
  	Emulation $emulator,
  	ModuleManager $moduleManager,
  	Plugin $plugin,
    ResourceInterface $resourceInterface,
    array $data = []
  ) {
		$this->integrationManager = $integrationManager;
		$this->url                = $url;
		$this->storeManager       = $storeManager; 
		$this->emulator           = $emulator;
		$this->moduleManager      = $moduleManager;
		$this->plugin             = $plugin;
		$this->resourceInterface  = $resourceInterface;
		parent::__construct($context, $data);

		if ($this->_request->getParam('section') !== 'wordpress') {
			return;
		}
		
		$this->success = false;
		
		try {
			$storeId = 0;

			if (($websiteId = (int)$this->_request->getParam('website')) !== 0) {
				$storeId = (int)$this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
			}

			if ($storeId === 0) {
				$storeId = (int)$this->_request->getParam('store');
			}

			if ($storeId === 0) {
				$storeId = (int)$this->storeManager->getDefaultStoreView()->getId();
			}

			$this->emulator->startEnvironmentEmulation($storeId);

			$this->integrationManager->runTests();
			
			$this->success = sprintf(
				'WordPress Integration is active. View your blog at <a href="%s" target="_blank">%s</a>.', 
				$this->url->getHomeUrl(), 
				$this->url->getHomeUrl()
			);
			
			$this->emulator->stopEnvironmentEmulation();
		} 
		catch (\Exception $e) {
			$this->emulator->stopEnvironmentEmulation();
			$this->exception = $e;
		}
	}

    /**
     * @return string
     */
	protected function _toHtml()
	{
		$messages = [];

		if ($exception = $this->exception) {
			$messages[] = $this->_getMessage($exception->getMessage(), 'error');
		}
		else if ($this->success) {
			$messages[] = $this->_getMessage($this->success);
			
			if ($msg = $this->_getYoastSeoMessage()) {
				$messages[] = $msg;
			}
		}
		
		if ($messages) {
			return '<div class="messages">' . implode("\n", $messages) . '</div>'. $this->_getExtraHtml();
		}
		
		return '';
	}

    /**
     * @return string
     */
	protected function _getExtraHtml()
	{
		$moduleVersion = $this->resourceInterface->getDbVersion('PSS_WordPress');
		
		return "
		<script>
			require(['jquery'], function($){
				$(document).ready(function() {
					document.getElementById('wordpress_setup-head').innerHTML = 'Magento WordPress Integration - v" . $moduleVersion . "';
				});
			});
		</script>
		";	
	}

    /**
     * @return string
     */
	protected function _getYoastSeoMessage()
	{
		$yoastPluginEnabled = $this->plugin->isEnabled('wordpress-seo/wp-seo.php');
		$yoastModuleEnabled = $this->moduleManager->isEnabled('PSS_WordPress_Yoast');

		if (!$yoastPluginEnabled && !$yoastModuleEnabled) {
			return $this->_getMessage(
				sprintf(
					'For the best SEO results, you should install the free <a href="%s" target="_blank">Yoast SEO WordPress plugin</a> and the free <a href="%s" target="_blank">Yoast SEO Magento extension</a>.', 
					self::YOAST_SEO_PLUGIN_URL,
					self::YOAST_SEO_MODULE_URL
				),
				'notice'
			);
		} 
		
		if (!$yoastPluginEnabled) {
			return $this->_getMessage(
				sprintf('For the best SEO results, you should install the free <a href="%s" target="_blank">Yoast SEO WordPress plugin</a>.', 'https://wordpress.org/plugins/wordpress-seo/'),
				'notice'
			);
		}
		
		if (!$yoastModuleEnabled) {
			return $this->_getMessage(
				sprintf(
					'You have installed the Yoast SEO plugin in WordPress. To complete the SEO integration, install the free <a href="%s" target="_blank">Yoast SEO Magento extension</a>.', 
					self::YOAST_SEO_MODULE_URL
				),
				'notice'
			);
		}
		return '';
	}

    /**
     * @param $msg
     * @param string $type
     * @return string
     */
	protected function _getMessage($msg, $type = 'success')
	{
		return sprintf('<div class="message message-%s %s"><div>%s</div></div>', $type, $type, $msg);
	}

    /**
     * @return Template
     */
	protected function _prepareLayout()
	{
		return parent::_prepareLayout();
	}
}
