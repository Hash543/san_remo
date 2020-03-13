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
namespace PSS\WordPress\Block\Adminhtml\Serializer\Post;

abstract class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \PSS\WordPress\Model\ResourceModel\Post\CollectionFactory
     */
    protected $postCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \PSS\WordPress\Helper\Data
     */
    protected $dataHelper;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \PSS\WordPress\Helper\Data $dataHelper
     * @param \PSS\WordPress\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \PSS\WordPress\Helper\Data $dataHelper,
        \PSS\WordPress\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->postCollectionFactory = $postCollectionFactory;
        $this->coreRegistry = $registry;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Set defaults
     *
     * @return void
     * @throws |Exception
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('ID');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setId('post_grid');
        $this->setVarNameFilter('post_filter');
    }

    /**
     * Instantiate and prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var  \PSS\WordPress\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->postCollectionFactory->create();
        $collection->addFieldToFilter('post_type', array('eq' => 'post'))
                   ->addFieldToFilter('post_status', 'publish');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Define grid columns
     *
     * @return $this
     * @throws  \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_post',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_post',
                'values' => $this->_getSelectedPost(),
                'align' => 'center',
                'index' => 'ID'
            ]
        );
        $this->addColumn(
            'post_title',
            ['header' => __('Title'), 'type' => 'text', 'index' => 'post_title', 'escape' => true]
        );
        $this->addColumn(
            'post_status',
            ['header' => __('Status'), 'type' => 'text', 'index' => 'post_status', 'escape' => true]
        );
        return parent::_prepareColumns();
    }

    /**
     * Get selected post ids for
     *
     * @return array
     *
     */
    protected function _getSelectedPost()
    {
        $posts = null;
        try {
            $posts = $this->getSelectedPosts();
        } catch (\Exception $e) {
            // Todo: don't do nothing
        }
        if ($posts === null) {
            $posts = $this->getPostByEntity();
        }
        return $posts;
    }

    /**
     * Get The Post By Entity
     * @return array
     */
    public abstract function getPostByEntity();

    /**
     * Ajax grid URL getter
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/wordpress/postgrid', ['_current' => true]);
    }

    /**
     * Disable mass action functionality
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        return $this;
    }
}