<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog product form gallery content
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method \Magento\Framework\Data\Form\Element\AbstractElement getElement()
 */
namespace PSS\GalleryFix\Block\Adminhtml\Product\Helper\Form\Gallery;

use Magento\Backend\Block\Media\Uploader;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;

class Content extends \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content
{

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        array $data = []
    )
    {
        parent::__construct($context, $jsonEncoder, $mediaConfig, $data);
    }

    /**
     * @return string
     */
    public function getImagesJson()
    {
        $value = $this->getElement()->getImages();
        if (is_array($value) &&
            array_key_exists('images', $value) &&
            is_array($value['images']) &&
            count($value['images'])
        ) {
            $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);

            if(is_array($images = $this->sortImagesByPosition($value['images']))){
                foreach ($images as &$image) {
                    $image['url'] = $this->_mediaConfig->getMediaUrl($image['file']);
                    try {
                        //if (is_readable($this->_mediaConfig->getMediaPath($image['file']))) {
                            $fileHandler = $mediaDir->stat($this->_mediaConfig->getMediaPath($image['file']));
                            $image['size'] = $fileHandler['size'];
                        //}
                    } catch (FileSystemException $e) {
                        $image['url'] = $this->getImageHelper()->getDefaultPlaceholderUrl('small_image');
                        $image['size'] = 0;
                        $this->_logger->warning($e);
                    }
                }
                return $this->_jsonEncoder->encode($images);
            }

        }
        return '[]';
    }

    /**
     * Sort images array by position key
     *
     * @param array $images
     * @return array
     */
    private function sortImagesByPosition($images)
    {
        if (is_array($images)) {
            usort($images, function ($imageA, $imageB) {
                return ($imageA['position'] < $imageB['position']) ? -1 : 1;
            });
        }
        return $images;
    }

    /**
     * @return \Magento\Catalog\Helper\Image
     * @deprecated 101.0.3
     */
    private function getImageHelper()
    {
        if ($this->imageHelper === null) {
            $this->imageHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Helper\Image::class);
        }
        return $this->imageHelper;
    }


}
