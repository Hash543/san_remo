<?php



namespace Firebear\ConfigurableProducts\Observer;

use Firebear\ConfigurableProducts\Helper\Data;
use Firebear\ConfigurableProducts\Model\ProductOptions;
use Firebear\ConfigurableProducts\Model\ProductOptionsFactory;
use Firebear\ConfigurableProducts\Model\ProductOptionsRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IcpOptionsSave implements ObserverInterface
{
    protected $helper;
    protected $productOptionsModel;
    protected $productOptionsRepository;

    /**
     * DimensionalShippingOptionsSave constructor.
     *
     * @param Data                     $helper
     * @param ProductOptionsRepository $productOptionsRepository
     * @param ProductOptionsFactory    $productOptionsModel
     */
    public function __construct(
        Data $helper,
        ProductOptionsRepository $productOptionsRepository,
        ProductOptionsFactory $productOptionsModel
    ) {
        $this->helper                   = $helper;
        $this->productOptionsRepository = $productOptionsRepository;
        $this->productOptionsModel      = $productOptionsModel;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($observer->getEvent()->getObject()->getEventPrefix() == 'catalog_product') {
            $data        = $observer->getEvent()->getObject()->getData();
            $productId   = $observer->getEvent()->getObject()->getId();
            $currentItem = $this->productOptionsRepository->getByProductId($productId);
            if (empty($data['size_code'])) {
                $data['size_code'] = "volumen";
            }
            if (empty($data['color_code'])) {
                $data['color_code'] = "color";
            }
            if (!empty($data['size_code']) && !empty($data['color_code'])) {
                if (!$currentItem) {
                    $currentItem = $this->productOptionsModel->create();
                }
                $currentItem->setData('product_id', $observer->getEvent()->getObject()->getId());
                foreach ($this->helper->getFields() as $field) {
                    if (isset($data[$field])) {
                        $currentItem->setData($field, $data[$field]);
                    }
                }
                if (!empty($data[ProductOptions::LINKED_ATTRIBUTE_IDS])) {
                    $linkedAttributes = implode(',', $data[ProductOptions::LINKED_ATTRIBUTE_IDS]);
                    $currentItem->setData(ProductOptions::LINKED_ATTRIBUTE_IDS, $linkedAttributes);
                }

                $this->productOptionsRepository->save($currentItem);
            } else {
                $this->productOptionsRepository->deleteByProductId($productId);
            }
        }
    }
}
