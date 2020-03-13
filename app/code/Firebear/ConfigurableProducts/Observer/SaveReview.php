<?php

namespace Firebear\ConfigurableProducts\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\Review;

class SaveReview implements ObserverInterface
{

    /**
     * @var SwatchesHelper
     */
    protected $productFactory;

    protected $objectManager;

    protected $customerSession;

    protected $reviewFactory;

    protected $ratingFactory;

    protected $reviewSession;

    protected $storeManager;

    /**
     * AbstractBlock constructor.
     *
     * @param SwatchesHelper|null $swatchesHelper
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Framework\Session\Generic $reviewSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->productFactory  = $productFactory;
        $this->objectManager   = $objectManager;
        $this->customerSession = $customerSession;
        $this->reviewSession   = $reviewSession;
        $this->reviewFactory   = $reviewFactory;
        $this->ratingFactory   = $ratingFactory;
        $this->storeManager    = $storeManager;
    }

    /**
     * Change default swatches template
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data            = $observer->getRequest()->getParams();
        $simpleProductId = $data['id'];
        $simpleProduct   = $this->productFactory->create()->load($simpleProductId);
        if ($simpleProduct->getTypeId() == 'simple') {
            $productIds = $this->objectManager->create(
                'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable'
            )
                ->getParentIdsByChild($simpleProductId);
            if (isset($productIds[0]) && $productIds[0]) {
                $product = $this->productFactory->create()->load($productIds[0]);
                if ($data) {
                    $rating = [];
                    if (isset($data['ratings']) && is_array($data['ratings'])) {
                        $rating = $data['ratings'];
                    }
                } else {
                    $rating = $observer->getRequest()->getParam('ratings', []);
                }
                if ($product->getId() && !empty($data)) {
                    $review = $this->reviewFactory->create()->setData($data);
                    $review->unsetData('review_id');
                    $validate = $review->validate();
                    if ($validate === true) {
                        try {
                            $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                                ->setEntityPkValue($product->getId())
                                ->setStatusId(Review::STATUS_PENDING)//STATUS_APPROVED
                                ->setCustomerId($this->customerSession->getCustomerId())
                                ->setStoreId($this->storeManager->getStore()->getId())
                                ->setStores([$this->storeManager->getStore()->getId()])
                                ->save();
                            foreach ($rating as $ratingId => $optionId) {
                                $this->ratingFactory->create()
                                    ->setRatingId($ratingId)
                                    ->setReviewId($review->getId())
                                    ->setCustomerId($this->customerSession->getCustomerId())
                                    ->addOptionVote($optionId, $product->getId());
                            }
                            $review->aggregate();
                        } catch (\Exception $e) {
                            $this->reviewSession->setFormData($data);
                        }
                    } else {
                        $this->reviewSession->setFormData($data);
                    }
                }
            }
        }

        return $this;
    }
}
