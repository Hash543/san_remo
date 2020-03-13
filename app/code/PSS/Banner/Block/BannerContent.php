<?php

namespace Pss\Banner\Block;


class BannerContent extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Cms\Block\Block
     */
    private $block;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Cms\Block\Block $block
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Block\Block $block,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->block = $block;
    }

    /**
     * @return string
     */
    public function getProductPageContent()
    {
        return $this->getBockText('banner_product_page');
    }

    private function getBockText($identifier)
    {
        return $this->block->getLayout()->createBlock('Magento\Cms\Block\Block')
            ->setBlockId($identifier)
            ->toHtml();
    }

}