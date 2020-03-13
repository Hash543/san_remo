<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Rule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons;

class Grid extends \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid {

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns() {

        parent::_prepareColumns();
        $this->addColumn(
            'customer_id',
            [
                'header' => __('Customer Id'),
                'index' => 'customer_id',
                'type' => 'number',
                'align' => 'center',
                'width' => '160'
            ]
        );
        $this->addColumn(
            'id_ticket_origin',
            [
                'header' => __('Id Ticket Origin'),
                'index' => 'id_ticket_origin',
                'align' => 'center',
                'width' => '160'
            ]
        );
        $this->addColumn(
            'id_ticket_destiny',
            [
                'header' => __('Id Ticket Destiny'),
                'index' => 'id_ticket_origin',
                'align' => 'center',
                'width' => '160'
            ]
        );
        $this->sortColumnsByOrder();
        return $this;
    }
}