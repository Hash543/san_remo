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

use PSS\WordPress\Model\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class OptionManager
{   
	/*
	 *
	 * @var array
	 *
	 */
	protected $data = [];
	
	/*
	 *
	 * @var StoreManagerInterface
	 *
	 */
	protected $storeManager;
	
	/*
	 *
	 * @var ResourceConnection
	 *
	 */
	protected $resourceConnection;

	/*
	 *
	 *
	 *
	 */
	public function __construct(
		   ResourceConnection $resourceConnection, 
		StoreManagerInterface $storeManager
	)
	{
		$this->resourceConnection = $resourceConnection;
		$this->storeManager       = $storeManager;
	}
	
	/*
	 * Get option value
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function getOption($key)
	{
		$storeId = $this->getStoreId();

		if (!isset($this->data[$storeId])) {
			$this->data[$storeId] = [];
		}
		
		if (!isset($this->data[$storeId][$key])) {
			$resource   = $this->resourceConnection;
			$connection = $resource->getConnection();

			$select = $connection->select()
				->from($resource->getTable('wordpress_option'), 'option_value')
				->where('option_name = ?', $key);

			$this->data[$storeId][$key] = $connection->fetchOne($select);
		}

		return $this->data[$storeId][$key];	
	}
	
	public function optionExists($key)
	{
		$resource   = $this->resourceConnection;
		$connection = $resource->getConnection();

		$select = $connection->select()
			->from($resource->getTable('wordpress_option'), 'option_value')
			->where('option_name = ?', $key)
			->limit(1);

		return $connection->fetchOne($select) !== false;
	}

	/*
	 * Get a site option.
	 * This is implemented in Multisite
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function getSiteOption($key)
	{
		return false;
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
}
