<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Rule\Model\Marketing;

class OptionsProvider implements \Magento\Framework\Data\OptionSourceInterface {

    /**
     * @var \PSS\CRM\Api\PromotionRepositoryInterface
     */
    private $promotionRepository;
    /**
     * OptionsProvider constructor.
     * @param \PSS\CRM\Api\PromotionRepositoryInterface $promotionRepository
     */
    public function __construct(
        \PSS\CRM\Api\PromotionRepositoryInterface $promotionRepository
    ) {
        $this->promotionRepository = $promotionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray() {
       $listMarketing = $this->promotionRepository->getListMarketing();
       $listMarketing = \PSS\CRM\Helper\PromotionService::getListMarketingFromWebserviceResponse($listMarketing);
       $options = [];
       /*$options[] = [
           'value' => '0',
           'label' => __('Ninguna')
       ];*/
       foreach ($listMarketing as $list) {
           if(isset($list['a:CodigoCRMListaMarketing']) && isset($list['a:ListaMarketingNombre'])) {
               $options[] = [
                   'value' => $list['a:CodigoCRMListaMarketing'],
                   'label' => $list['a:ListaMarketingNombre']
               ];
           }
       }
       return $options;
    }
}