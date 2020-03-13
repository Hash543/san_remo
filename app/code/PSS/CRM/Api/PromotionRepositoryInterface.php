<?php
namespace PSS\CRM\Api;

interface PromotionRepositoryInterface
{

    /**
     * Returns information by user email
     *
     * @api
     * @param string $email User Email
     * @return mixed
     */

    public function get($ruleId);
    public function xmlParse($xml);
    //public function customer_update($customer, $array);

    /**
     * @return array
     */
    public function getListMarketing();

    /**
     * @param string $couponCode
     * @return array
     */
    public function getCoupon($couponCode);
    public function check($type, $value);
    public function create($rule);
    public function createCoupon($rule);
    public function delete($rule);
    public function deleteCoupon($rule);
    public function modify($rule);
    public function modifyCoupon($rule);


}
