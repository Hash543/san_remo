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
use PSS\WordPress\Model\ResourceConnection;
use PSS\WordPress\Model\OptionManager;
use PSS\WordPress\Model\ShortcodeManager;
use PSS\WordPress\Model\PostTypeManager\Proxy as PostTypeManager;
use PSS\WordPress\Model\TaxonomyManager\Proxy as TaxonomyManager;
use PSS\WordPress\Model\Url;
use PSS\WordPress\Model\Factory;
use PSS\WordPress\Helper\Date as DateHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http as Request;
use Magento\Store\Model\StoreManagerInterface;

class Context
{
	/*
	 *
	 * @var 
	 *
	 */
	protected $resourceManager;
	
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
	protected $shortcodeManager;
	
	/*
	 *
	 * @var 
	 *
	 */
	protected $postTypeManager;
	
	/*
	 *
	 * @var 
	 *
	 */
	protected $taxonomyManager;
	
	/*
	 *
	 * @var 
	 *
	 */
	protected $url;
	
	/*
	 *
	 * @var 
	 *
	 */
	protected $factory;
	
	/*
	 *
	 * @var 
	 *
	 */
	protected $dateHelper;
	
	/*
	 *
	 * @var 
	 *
	 */
	protected $registry;
	
	/*
	 *
	 * @var 
	 *
	 */
	protected $customerSession;
	
	/*
	 *
	 * @var 
	 *
	 */
	protected $request;

	/*
	 *
	 * @var StoreManagerInterface
	 *
	 */
	protected $storeManager;

	/*
	 *
	 *
	 *
	 */
	public function __construct(
		ResourceConnection $resourceConnection,
		OptionManager $optionManager,
		ShortcodeManager $shortcodeManager,
		PostTypeManager $postTypeManager,
		TaxonomyManager $taxonomyManager,
		Url $url,
		Factory $factory,
		DateHelper $dateHelper,
		Registry $registry,
		Layout $layout,
		CustomerSession $customerSession,
		Request $request,
		StoreManagerInterface $storeManager
	)
	{
		$this->resourceConnection = $resourceConnection;
		$this->optionManager      = $optionManager;
		$this->shortcodeManager   = $shortcodeManager;
		$this->postTypeManager    = $postTypeManager;
		$this->taxonomyManager    = $taxonomyManager;
		$this->url                = $url;
		$this->factory            = $factory;
		$this->dateHelper         = $dateHelper;
		$this->registry           = $registry;
		$this->layout             = $layout;
		$this->customerSession    = $customerSession;
		$this->request            = $request;
		$this->storeManager       = $storeManager;
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function getResourceConnection()
	{
		return $this->resourceConnection;
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getOptionManager()
	{
		return $this->optionManager;
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function getShortcodeManager()
	{
		return $this->shortcodeManager;
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getTaxonomyManager()
	{
		return $this->taxonomyManager;
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getPostTypeManager()
	{
		return $this->postTypeManager;
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function getUrl()
	{
		return $this->url;
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getFactory()
	{
		return $this->factory;
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getDateHelper()
	{
		return $this->dateHelper;
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getRegistry()
	{
		return $this->registry;
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function getCustomerSession()
	{
		return $this->customerSession;
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/*
	 *
	 *
	 * @return StoreManagerInterface
	 */
	public function getStoreManager()
	{
		return $this->storeManager;
	}
}
