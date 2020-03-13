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
namespace PSS\WordPress\Model\Sitemap\ItemProvider;

/* Constructor Args */
use \PSS\WordPress\Model\Factory;
use \Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
/* Misc */
use \Magento\Framework\App\ObjectManager;

abstract class AbstractItemProvider/* implements ItemProviderInterface*/
{	
	/**
	 * @var \PSS\WordPress\Model\Factory
	 */
	protected $factory;
	
	/**
	 * @var \Magento\Store\Model\App\Emulation;
	 */
	protected $emulation;
    /**
     * @var \Magento\Sitemap\Model\SitemapItemInterfaceFactory
     */
	protected $itemFactory;
    /**
     * @var StoreManagerInterface
     */
	protected $storeManager;

    /**
     * AbstractItemProvider constructor.
     * @param Factory $factory
     * @param Emulation $emulation
     * @param StoreManagerInterface $storeManager
     */
	public function __construct(Factory $factory, Emulation $emulation, StoreManagerInterface $storeManager)
	{
		$this->emulation  = $emulation;
		$this->factory  = $factory;
        $this->storeManager = $storeManager;
		// OM required as SitemapItemInterfaceFactory is not present in Magento 2.2 and below so constructor injection breaks compilation
		$this->itemFactory = ObjectManager::getInstance()->create('Magento\Sitemap\Model\SitemapItemInterfaceFactory');
	}
	
	/*
	 *
	 *
	 * @param int $storeId
	 */
	final public function getItems($storeId)
	{
		try {
			$this->emulation->startEnvironmentEmulation($storeId);
			
			$items = $this->_getItems($storeId);
			
			$this->emulation->stopEnvironmentEmulation();
		
			return $items;
		}
		catch (\Exception $e) {
			$this->emulation->stopEnvironmentEmulation();
			
			throw $e;
		}
		
		return [];
	}
}
