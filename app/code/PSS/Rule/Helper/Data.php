<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Rule\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
    /**
     * Contant Values
     */
    const NO_MARKETING_LIST = 'no-marketing-list';
    const ATTRIBUTE_LIST_MARKETING = 'list_marketing_id';
    /**
     * Constant Attributes
     */
    const CRM_ID = 'crm_id';
    const ERP_ID = 'erp_id';

    /**
     * Attributes Coupon
     */
    const ID_TICKET_ORIGIN = 'id_ticket_origin';
    const ID_TICKET_DESTINY = 'id_ticket_destiny';
    const CUSTOMER_ID = 'customer_id';
    const START_DATE = 'start_date';
}