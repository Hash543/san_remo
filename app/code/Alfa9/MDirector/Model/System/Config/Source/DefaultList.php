<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Model\System\Config\Source;

class DefaultList implements \Magento\Framework\Option\ArrayInterface {

    private $options;
    /**
     * @var \Alfa9\MDirector\Model\Api\ListGroup
     */
    private $listGroup;
    /**
     * DefaultList constructor.
     * @param \Alfa9\MDirector\Model\Api\ListGroup $listGroup
     */
    public function __construct(
        \Alfa9\MDirector\Model\Api\ListGroup $listGroup
    ) {
        $this->listGroup = $listGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if(is_null($this->options)){
            $this->options[] = ['value' => '', 'label' => __('-- Please select --')];
            $result = $this->listGroup->fetch();
            if($result && is_array($result) && isset($result['lists']) && is_array($result['lists'])){
                foreach ($result['lists'] as $list) {
                    $this->options[] = [
                        'value' => $list['id'],
                        'label' => $list['name']
                    ];
                }
            }
        }
        return $this->options;
    }
}