<?php


namespace Pss\Seo\Block;

class Seotext extends \Magento\Framework\View\Element\Template
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
    public function getSeoTextToCategory()
    {
        return $this->getSeoText('category_seo_text');
    }

    public function getSeoTextToHome()
    {
        return $this->getSeoText('home_seo_text');
    }

    private function getSeoText($identifier)
    {
        return $this->block->getLayout()->createBlock('Magento\Cms\Block\Block')
            ->setBlockId($identifier)
            ->toHtml();
    }

}
