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

use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\StoreManagerInterface;
use PSS\WordPress\Model\Factory;
use PSS\WordPress\Model\OptionManager;
use PSS\WordPress\Model\Network;

class TaxonomyManager
{
	/*
	 * @var 
	 */
	protected $moduleManager;
	
	/*
	 * @var 
	 */
	protected $storeManager;
	
	/*
	 *
	 */
	protected $optionManager;
	
	/*
	 *
	 */
	protected $network;
	
	/*
	 * @var array
	 */
	protected $taxonomies = [];

	/*
	 *
	 * @param  ModuleManaher $moduleManaher
	 * @return void
	 */
	public function __construct(
		        ModuleManager $moduleManager, 
		StoreManagerInterface $storeManager, 
		              Factory $factory, 
		        OptionManager $optionManager,
		              Network $network
  )
	{
		$this->moduleManager   = $moduleManager;
		$this->storeManager    = $storeManager;
		$this->factory         = $factory;
		$this->optionManager   = $optionManager;
		$this->network         = $network;

		$this->load();
	}
	
	/*
	 *
	 *
	 * @return $this
	 */
	public function load()
	{
		$storeId = $this->getStoreId();
		
		if (isset($this->taxonomies[$storeId])) {
			return $this;
		}
		
		if ($taxonomyData = $this->getTaxonomyDataFromAddon()) {
			foreach($taxonomyData as $taxonomy) {
				$this->registerTaxonomy(
					$this->getTaxonomyFactory()->create()->addData($taxonomy)
				);
			}
		}
		else {
			$bases = array(
				'category' => $this->optionManager->getOption('category_base') ? $this->optionManager->getOption('category_base') : 'category',
				'post_tag' => $this->optionManager->getOption('tag_base')      ? $this->optionManager->getOption('tag_base')      : 'tag',
			);
	
			$blogPrefix = $this->network->getBlogId() === 1;
			
			if ($blogPrefix) {
				foreach($bases as $baseType => $base) {
					if ($blogPrefix && $base && strpos($base, '/blog') === 0) {
						$bases[$baseType] = substr($base, strlen('/blog'));	
					}
				}
			}

			$this->registerTaxonomy(
				$this->getTaxonomyFactory()->create()->addData([
					'type' => 'category',
					'taxonomy_type' => 'category',
					'labels' => array(
						'name' => 'Categories',
						'singular_name' => 'Category',
					),
					'public' => true,
					'hierarchical' => true,
					'rewrite' => array(
						'hierarchical' => true,
						'slug' => $bases['category'],
						'with_front' => (int)($bases['category'] === 'category'),
					),
					'_builtin' => true,
				])
			);
			
			$this->registerTaxonomy(
				$this->getTaxonomyFactory()->create()->addData([
					'type' => 'post_tag',
					'taxonomy_type' => 'post_tag',
					'labels' => array(
						'name' => 'Tags',
						'singular_name' => 'Tag',
					),
					'public' => true,
					'hierarchical' => false,
					'rewrite' => array(
						'slug' => $bases['post_tag'],
						'with_front' => (int)($bases['post_tag'] === 'tag'),
					),
					'_builtin' => true,
				])
			);
		}

		return $this;
	}
	
	/*
	 * Register a taxonomy
	 *
	 * @param  Taxonomy $taxonomy
	 * @return $this
	 */
	public function registerTaxonomy(\PSS\WordPress\Model\Taxonomy $taxonomy)
	{
		$storeId = $this->getStoreId();
		
		if (!isset($this->taxonomies[$storeId])) {
			$this->taxonomies[$storeId] = [];
		}

		$this->taxonomies[$storeId][$taxonomy->getTaxonomy()] = $taxonomy;
		
		return $this;
	}
	
	/*
	 *
	 *
	 * @return
	 */
	public function getTaxonomyFactory()
	{
		return $this->factory->get('TaxonomyFactory');
	}

	/*
	 *
	 *
	 * @return false|Type
	 */
	public function getTaxonomy($taxonomy = null)
	{
		if ($taxonomies = $this->getTaxonomies()) {
			if ($taxonomy === null) {
				return $taxonomies;
			}
			else if (isset($taxonomies[$taxonomy])) {
				return $taxonomies[$taxonomy];
			}
		}
		
		return false;
	}

	/*
	 *
	 *
	 * @return false|array
	 */
	public function getTaxonomies()
	{
		$storeId = $this->getStoreId();
		
		$this->load();
		
		return isset($this->taxonomies[$storeId]) ? $this->taxonomies[$storeId] : false;		
	}
	
	
	/*
	 *
	 *
	 * @return bool
	 */
	protected function isAddonEnabled()
	{
		return $this->moduleManager->isOutputEnabled('PSS_WordPress_PostTypeTaxonomy');
	}

	/*
	 *
	 *
	 * @return int
	 */
	protected function getStoreId()
	{
		return (int)$this->storeManager->getStore()->getId();
	}
	
	/*
	 *
	 *
	 *
	 * @return false
	 */
	public function getTaxonomyDataFromAddon()
	{
		return false;
	}
}
