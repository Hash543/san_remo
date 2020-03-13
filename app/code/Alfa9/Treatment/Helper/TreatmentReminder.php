<?php

namespace Alfa9\Treatment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class TreatmentReminder extends AbstractHelper
{
    /**
     * @var \Alfa9\Treatment\Model\TreatmentFactory
     */
    private $treatmentFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    private $timezone;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    private $imageBuilder;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilderFactory
     */
    private $transportBuilderFactory;
    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $emulation;

    /**
     * TreatmentReminder constructor.
     * @param Context $context
     * @param \Alfa9\Treatment\Model\TreatmentFactory $treatmentFactory
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param \Magento\Framework\Mail\Template\TransportBuilderFactory $transportBuilderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Store\Model\App\Emulation $emulation
     */
    public function __construct(
        Context $context,
        \Alfa9\Treatment\Model\TreatmentFactory $treatmentFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneFactory $timezone,
        \Magento\Framework\Mail\Template\TransportBuilderFactory $transportBuilderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Store\Model\App\Emulation $emulation
    )
    {
        parent::__construct($context);;
        $this->treatmentFactory = $treatmentFactory;
        $this->timezone = $timezone;
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->imageBuilder = $imageBuilder;
        $this->urlBuilder = $urlBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilderFactory = $transportBuilderFactory;
        $this->emulation = $emulation;
    }

    public function getCurrentTreatment($today = null)
    {
        $reminderEnabled = $this->scopeConfig->getValue('pss_treatments/general/enable_reminder');

        if($reminderEnabled){
            $currentDate = ($today) ? date('Y-m-d', strtotime($today)) : date('Y-m-d');

            $treatmentDates = $this->treatmentFactory->create()->getCollection()
                ->addFieldToFilter('delivery_days', $currentDate);

            foreach ($treatmentDates as $treatmentDate) {
                $customerEmail = $treatmentDate->getCustomerEmail();
                $productSku = $treatmentDate->getProductSku();
                $this->sendEmail($customerEmail, $productSku);
            }
        }

        return $this;
    }

    private function sendEmail($customerEmail, $productSku)
    {
        try {
            $productData = $this->getProductVariables($productSku);

            /** @var \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder */
            $transportBuilder = $this->transportBuilderFactory->create();

            $transportBuilder->setTemplateIdentifier(
                'treatment_email'
            )->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => 1
                ]
            )->setTemplateVars(
                ['customer' => $this->converToDataObject(['email' => $customerEmail]),
                    'product' => $this->converToDataObject($productData)]
            )->setFrom(
                'general'
            )->addTo(
                $customerEmail, $customerEmail
            );

            $transportBuilder->getTransport()->sendMessage();
        } catch (\Exception $exception) {
        }
    }

    /**
     * @param $data
     * @return \Magento\Framework\DataObject
     */
    private function converToDataObject($data)
    {
        /** @var \Magento\Framework\DataObject $dataObject */
        $dataObject = new \Magento\Framework\DataObject();
        return $dataObject->setData($data);
    }

    /**
     * @param $productSku
     * @return mixed
     */
    private function getProductVariables($productSku)
    {
        $this->emulation->startEnvironmentEmulation(1);
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productFactory->create();
        $product->load($product->getIdBySku($productSku));

        $encryptSku = base64_encode($productSku);
        $urlAddToCart = $this->urlBuilder->getUrl("treat/treatment/addproduct", ['k' => $encryptSku]);

        // this is causing issue Required parameter 'theme_dir' was not passed
        $imageUrl = $this->getImage($product, 'category_page_list')->getImageUrl();
        $data = [
            'name' => $product->getName(),
            'image_url' => $imageUrl,
            'description' => $product->getDescription(),
            'price' => round($product->getPrice(), 2),
            'addCart' => $urlAddToCart
        ];
        $this->emulation->stopEnvironmentEmulation();
        return $data;
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    private function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }
}