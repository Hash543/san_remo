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
use PSS\WordPress\Model\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;

/* Misc */
use PSS\WordPress\Model\Integration\IntegrationException;

class WPConfig
{   
	/*
	 *
	 * @var DirectoryList
	 *
	 */
	protected $wpDirectoryList;
	
	/*
	 *
	 * @var StoreManagerInterface
	 *
	 */
	protected $storeManager;
	
	/*
	 *
	 * @var array
	 *
	 */
	protected $data = [];
	
	/*
	 *
	 *
	 */
	public function __construct(DirectoryList $wpDirectoryList, StoreManagerInterface $storeManager)
	{
		$this->storeManager    = $storeManager;
		$this->wpDirectoryList = $wpDirectoryList;
	}

	/*
	 *
	 *
	 */
	protected function initialise()
	{
		$storeId = $this->getStoreId();
		
		if (!isset($this->data[$storeId])) {
			$this->data[$storeId] = false;

			$configFile = $this->getConfigFilePath();

			if (!$configFile || !is_file($configFile)) {
				throw new \Exception('WordPress doesn\'t appear to be installed.');
			}

			$wpConfig = file_get_contents($configFile);

			# Cleanup comments
			$wpConfig = str_replace("\n", "\n\n", $wpConfig);
			$wpConfig = preg_replace('/\n\#[^\n]{1,}\n/', "\n", $wpConfig);
			$wpConfig = preg_replace('/\n\\/\/[^\n]{1,}\n/', "\n", $wpConfig);
			$wpConfig = preg_replace('/\n\/\*.*\*\//Us', "\n", $wpConfig);

			if (!preg_match_all('/define\([\s]*["\']{1}([A-Z_0-9]+)["\']{1}[\s]*,[\s]*(["\']{1})([^\\2]*)\\2[\s]*\)/U', $wpConfig, $matches)) {
				IntegrationException::throwException('Unable to extract values from wp-config.php');
			}

			$this->data[$storeId] = array_combine($matches[1], $matches[3]);
			
			if (preg_match_all('/define\([\s]*["\']{1}([A-Z_0-9]+)["\']{1}[\s]*,[\s]*(true|false|[0-9]{1,})[\s]*\)/U', $wpConfig, $matches)) {			
				$temp = array_combine($matches[1], $matches[2]);
				
				foreach($temp as $k => $v) {
					if ($v === 'true') {
						$this->data[$storeId][$k] = true;
					}
					else if ($v === 'false') {
						$this->data[$storeId][$k] = false;
					}
					else {
						$this->data[$storeId][$k] = $v;
					}
				}
			}

			if (preg_match('/\$table_prefix[\s]*=[\s]*(["\']{1})([a-zA-Z0-9_]+)\\1/', $wpConfig, $match)) {
				$this->data[$storeId]['DB_TABLE_PREFIX'] = $match[2];
			}
			else {
				$this->data[$storeId]['DB_TABLE_PREFIX'] = 'wp_';
			}
		}
	}
	
	/*
	 *
	 *
	 * @param  string|null $key = null
	 * @return mixed
	 */
	public function getData($key = null)
	{
		$storeId = $this->getStoreId();
		
		$this->initialise();
		
		if (is_null($key)) {
			return isset($this->data[$storeId]) ? $this->data[$storeId] : false;
		}
		
		return isset($this->data[$storeId][$key]) ? $this->data[$storeId][$key] : false;
	}
	
	/*
	 * Get the store ID
	 *
	 * @return int
	 */
	protected function getStoreId()
	{
		return (int)$this->storeManager->getStore()->getId();
	}
	
	/*
	 * Get the path to the wp-config.php file
	 *
	 * @return string
	 */
	public function getConfigFilePath()
	{
		return ($basePath = $this->wpDirectoryList->getBasePath()) ? $basePath . '/wp-config.php' : false;
	}
}
