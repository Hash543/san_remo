<?php
/**
 * @author Israel Yasis
 */

namespace Alfa9\ConfigurableProduct\Block\Product\View;

/**
 * Class Gallery
 * @package Alfa9\ConfigurableProduct\Block\Product\View
 */
class Gallery extends \Magento\Catalog\Block\Product\View\Gallery {
    /**
     * Retrieve product images in JSON format
     *
     * @return string
     */
    public function getGalleryImagesJson() {
        $product = $this->getProduct();
        $entries = $product->getMediaGalleryEntries();

        foreach($this->getGalleryImages() as $image) {
            $imageHasType = true;
            //$imageHasType = false;
            /** @var \Magento\Catalog\Model\Product\Gallery\Entry $entry */
            /** Filter the images with only a Role or type */
            /*foreach ($entries as $entry) {
                $types = $entry->getTypes();
                if($image->getId() == $entry->getId() && count($types) > 0) {
                    $imageHasType = true;
                    break;
                }
            }*/
            if($imageHasType) {
                $imagesItems[] = [
                    'thumb' => $image->getData('small_image_url'),
                    'img' => $image->getData('medium_image_url'),
                    'full' => $image->getData('large_image_url'),
                    'caption' => ($image->getLabel() ? : $this->getProduct()->getName()),
                    'position' => $image->getPosition(),
                    'isMain' => $this->isMainImage($image),
                    'type' => str_replace('external-', '', $image->getMediaType()),
                    'videoUrl' => $image->getVideoUrl(),
                ];
            }
        }

        if(empty($imagesItems)) {
            $imagesItems[] = [
                'thumb' => $this->_imageHelper->getDefaultPlaceholderUrl('thumbnail'),
                'img' => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
                'full' => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
                'caption' => '',
                'position' => '0',
                'isMain' => true,
                'type' => 'image',
                'videoUrl' => null,
            ];
        }

        return json_encode($imagesItems);
    }
}