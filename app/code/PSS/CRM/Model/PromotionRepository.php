<?php
namespace PSS\CRM\Model;

use PSS\CRM\Model\Api\PromotionService;

class PromotionRepository implements \PSS\CRM\Api\PromotionRepositoryInterface
{
    protected $_promotionFactory;
    protected $_promotionService;
    const ALTA = 'Alta';
    const MODIFY = 'Modificacion';
    const SEARCH = "Ya existe en el CRM";


    public function __construct(
        PromotionService $promotionService

    )
    {
        $this->_promotionService = $promotionService;

    }

    /**
     * Return if user exists by user email
     *
     * @api
     * @param string $email Customer email
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */

// Data type and Error check
    public function check($type='string', $value=null)
    {

            if (is_array($value)){
                return '';
            }else if ($type == 'date') {

                return is_null($value) ? '' : $value;

            } else if ($type == 'bool') {

                return isset($value) && $value == 'True' ? true : false;

            } else {
                return is_null($value) ? '' : $value;
            }


    }

    // XML Parse
    public function xmlParse($xml)
    {

        if (is_string($xml)) {
            $p = new \Magento\Framework\Xml\Parser;
            $p->loadXML($xml);
            $array = $p->xmlToArray();
            return $array;
        }
        return [];
    }




    public function get($ruleId)
    {
        //TODO REALIZAR CONSULTA A CRM
        $xml = $this->_promotionService->query($ruleId);

        $array = $this->xmlParse($xml);
        $result = [];
        try {
                if(isset($array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:promociones']['a:Promocion']))
                {
                    $promo_array = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:promociones']['a:Promocion'];

                    $result[] = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:resultado'];
                    $result[] = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:resultadoDescripcion'];
                    $result[] = $promo_array['a:CodigoPromocion'];
                    $result[] = $promo_array['a:Descripcion'];
                    $result[] = $promo_array['a:FechaDeFin'];
                    $result[] = $promo_array['a:FechaDeInicio'];
                    $result[] = $promo_array['a:IDsListasMarketing'];
                    $result[] = $promo_array['a:URLBanner'];
                    $result[] = $promo_array['a:URLPromocion'];

                }
                else{
                    $promo_array = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult'];

                    $result[] = $promo_array['a:resultado'];
                    $result[] = $promo_array['a:resultadoDescripcion'];

                }
            return $result;


        } catch(\Exception $e) {
                $result[] = '1';
                $result[] = $e->getMessage();
        }


    }

    /**
     * {@inheritdoc}
     */
    public function getListMarketing() {

        $xml = $this->_promotionService->getlistSync();
        $array = $this->xmlParse($xml);
        if(isset($array['s:Envelope']['s:Body']['WcfGestionarListaMarketingResponse'])) {
            $response = $array['s:Envelope']['s:Body']['WcfGestionarListaMarketingResponse'];
        } else {
            $response['WcfGestionarListaMarketingResult'] = [
                'a:resultado' => 1,
                'a:resultaDoDescription' => __('Error recuperando los valores')
            ];
        }
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getCoupon($couponCode)
    {
        $xml = $this->_promotionService->getcouponSync($couponCode);
        $array = $this->xmlParse($xml);
        $response = [];
        if(isset($array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult'])) {
            $response =  $array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult'];
        } else {
            $response['WcfGestionarValeResult'] = [
                'a:resultado' => 1,
                'a:resultaDoDescription' => __('Error recuperando los valores')
            ];
        }
        return $response;

    }

// Create/ Modify Promotion Code
    public function create($rule)
    {
        //Update NEW CREATED DATA ON CRM
        $xml = $this->_promotionService->creationSync($rule, self::ALTA);

        $array = $this->xmlParse($xml);
        $result = [];

        try{
            if(isset($array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']))
            {
                $result_code = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:resultado'];
                $result_description = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:resultadoDescripcion'];
                $str = strpos($result_description, self::SEARCH) ;
                if ($result_code == "1" && strpos($result_description, self::SEARCH) == 0)
                {
                    $xml = $this->_promotionService->creationSync($rule, self::MODIFY);
                    $array = $this->xmlParse($xml);
                }

                // Return Values, Either for Creation of Modification
                if(isset($array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:promociones']['a:Promocion']))
                {
                    $promo_array = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:promociones']['a:Promocion'];

                    $result[] = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:resultado'];
                    $result[] = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:resultadoDescripcion'];
                    $result[] = $promo_array['a:CodigoPromocion'];
                    $result[] = $promo_array['a:Descripcion'];
                    $result[] = $promo_array['a:FechaDeFin'];
                    $result[] = $promo_array['a:FechaDeInicio'];
                    $result[] = $promo_array['a:IDsListasMarketing'];
                    $result[] = $promo_array['a:URLBanner'];
                    $result[] = $promo_array['a:URLPromocion'];

                }
                else{
                    $promo_array = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult'];

                    $result[] = $promo_array['a:resultado'];
                    $result[] = $promo_array['a:resultadoDescripcion'];

                }
                return $result;


            }

        }catch(\Exception $e) {
            $result[] = '1';
            $result[] = $e->getMessage();
        }


    }


    // Create/ Modify Coupon Code / Codigo Vale
    public function createCoupon($rule)
    {
        //Update NEW CREATED DATA ON CRM
        $xml = $this->_promotionService->createCouponSync($rule, self::ALTA);

        $array = $this->xmlParse($xml);
        $result = [];

        try{
            if(isset($array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult']))
            {
                $result_code = $array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult']['a:resultado'];
                $result_description = $array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult']['a:resultadoDescripcion'];
                $str = strpos($result_description, self::SEARCH) ;
                if ($result_code == "1" && strpos($result_description, self::SEARCH) == 0)
                {
                    $xml = $this->_promotionService->createCouponSync($rule, self::MODIFY);
                    $array = $this->xmlParse($xml);
                }

                // Return Coupon Values, Either for Creation of Modification
                if(isset($array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult']['a:vales']['a:Vale']))
                {
                    $coupon_array = $array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult']['a:vales']['a:Vale'];

                    $result[] = $array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult']['a:resultado'];
                    $result[] = $array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult']['a:resultadoDescripcion'];
                    $result[] = $coupon_array['a:CodigoCliente'];
                    $result[] = $coupon_array['a:CodigoDeVale'];
                    $result[] = $coupon_array['a:Estado'];
                    $result[] = $coupon_array['a:FechaDeAlta'];
                    $result[] = $coupon_array['a:FechaFin'];
                    $result[] = $coupon_array['a:FechaInicio'];
                    $result[] = $coupon_array['a:FechaModificacion'];
                    $result[] = $coupon_array['a:PromocionConsumo'];
                    $result[] = $coupon_array['a:TicketDestino'];
                    $result[] = $coupon_array['a:TicketOrigen'];

                }
                else{
                    $coupon_array = $array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult'];

                    $result[] = $coupon_array['a:resultado'];
                    $result[] = $coupon_array['a:resultadoDescripcion'];

                }
                return $result;

            }

        }catch(\Exception $e) {
            $result[] = '1';
            $result[] = $e->getMessage();
        }




    }

    // Delete a Promotion Code
    public function delete($rule)
{
    //DELETE CUSTOMER DATA ON CRM
    $xml = $this->_promotionService->deletionSync($rule);
    $array = $this->xmlParse($xml);
    $result = [];

    try {
        if(isset($array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:promociones']['a:Promocion']))
        {
            $promo_array = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:promociones']['a:Promocion'];

            $result[] = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:resultado'];
            $result[] = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:resultadoDescripcion'];

            $result[] = $promo_array['a:CodigoPromocion'];
            $result[] = $promo_array['a:Descripcion'];
            $result[] = $promo_array['a:FechaDeFin'];
            $result[] = $promo_array['a:FechaDeInicio'];
            $result[] = $promo_array['a:IDsListasMarketing'];
            $result[] = $promo_array['a:URLBanner'];
            $result[] = $promo_array['a:URLPromocion'];
            return $result;

        }

    } catch(\Exception $e) {
        $result[] = '1';
        $result[] = $e->getMessage();
    }


}

// Delete Coupon Code/ Codigo Vale

    public function deleteCoupon($rule)
    {
        //DELETE CUSTOMER DATA ON CRM
        $xml = $this->_promotionService->deletionSync($rule);
        $array = $this->xmlParse($xml);
        $result = [];

        try {
            if(isset($array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult']['a:vales']['a:Vale']))
            {
                $coupon_array = $array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult']['a:vales']['a:Vale'];

                $result[] = $array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult']['a:resultado'];
                $result[] = $array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult']['a:resultadoDescripcion'];

                $result[] = $coupon_array['a:CodigoCliente'];
                $result[] = $coupon_array['a:CodigoDeVale'];
                $result[] = $coupon_array['a:Estado'];
                $result[] = $coupon_array['a:FechaDeAlta'];
                $result[] = $coupon_array['a:FechaFin'];
                $result[] = $coupon_array['a:FechaInicio'];
                $result[] = $coupon_array['a:FechaModificacion'];
                $result[] = $coupon_array['a:PromocionConsumo'];
                $result[] = $coupon_array['a:TicketDestino'];
                $result[] = $coupon_array['a:TicketOrigen'];

                return $result;

            }

        } catch(\Exception $e) {
            $result[] = '1';
            $result[] = $e->getMessage();
        }

    }


    // Modify a Promotion Code
    public function modify($rule)
    {
        ///UPDATE PROMOTION DATA ON CRM
        $xml = $this->_promotionService->modifySync($rule);

        $array = $this->xmlParse($xml);
        $result = [];

        try{

            // Return Values, Modification
            if(isset($array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:promociones']['a:Promocion']))
            {
                $promo_array = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:promociones']['a:Promocion'];

                $result[] = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:resultado'];
                $result[] = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult']['a:resultadoDescripcion'];
                $result[] = $promo_array['a:CodigoPromocion'];
                $result[] = $promo_array['a:Descripcion'];
                $result[] = $promo_array['a:FechaDeFin'];
                $result[] = $promo_array['a:FechaDeInicio'];
                $result[] = $promo_array['a:IDsListasMarketing'];
                $result[] = $promo_array['a:URLBanner'];
                $result[] = $promo_array['a:URLPromocion'];

            }
            else{
                $promo_array = $array['s:Envelope']['s:Body']['WcfGestionarPromocionResponse']['WcfGestionarPromocionResult'];

                $result[] = $promo_array['a:resultado'];
                $result[] = $promo_array['a:resultadoDescripcion'];

            }
            return $result;




        }catch(\Exception $e) {
            $result[] = '1';
            $result[] = $e->getMessage();
        }

    }


    // Modify Coupon Code / Codigo Vale
    public function modifyCoupon($rule)
    {
        //Update NEW CREATED DATA ON CRM
        $xml = $this->_promotionService->modifyCouponSync($rule);

        $array = $this->xmlParse($xml);
        $result = [];

        try{


                // Return Coupon Values,  Modification
                if(isset($array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult']['a:vales']['a:Vale']))
                {
                    $coupon_array = $array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult']['a:vales']['a:Vale'];

                    $result[] = $array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult']['a:resultado'];
                    $result[] = $array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult']['a:resultadoDescripcion'];
                    $result[] = $coupon_array['a:CodigoCliente'];
                    $result[] = $coupon_array['a:CodigoDeVale'];
                    $result[] = $coupon_array['a:Estado'];
                    $result[] = $coupon_array['a:FechaDeAlta'];
                    $result[] = $coupon_array['a:FechaFin'];
                    $result[] = $coupon_array['a:FechaInicio'];
                    $result[] = $coupon_array['a:FechaModificacion'];
                    $result[] = $coupon_array['a:PromocionConsumo'];
                    $result[] = $coupon_array['a:TicketDestino'];
                    $result[] = $coupon_array['a:TicketOrigen'];

                }
                else{
                    $coupon_array = $array['s:Envelope']['s:Body']['WcfGestionarValeResponse']['WcfGestionarValeResult'];

                    $result[] = $coupon_array['a:resultado'];
                    $result[] = $coupon_array['a:resultadoDescripcion'];

                }
                return $result;



        }catch(\Exception $e) {
            $result[] = '1';
            $result[] = $e->getMessage();
        }



    }
}
