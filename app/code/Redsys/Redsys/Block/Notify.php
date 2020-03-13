<?php
/**
* NOTA SOBRE LA LICENCIA DE USO DEL SOFTWARE
* 
* El uso de este software está sujeto a las Condiciones de uso de software que
* se incluyen en el paquete en el documento "Aviso Legal.pdf". También puede
* obtener una copia en la siguiente url:
* http://www.redsys.es/wps/portal/redsys/publica/areadeserviciosweb/descargaDeDocumentacionYEjecutables
* 
* Redsys es titular de todos los derechos de propiedad intelectual e industrial
* del software.
* 
* Quedan expresamente prohibidas la reproducción, la distribución y la
* comunicación pública, incluida su modalidad de puesta a disposición con fines
* distintos a los descritos en las Condiciones de uso.
* 
* Redsys se reserva la posibilidad de ejercer las acciones legales que le
* correspondan para hacer valer sus derechos frente a cualquier infracción de
* los derechos de propiedad intelectual y/o industrial.
* 
* Redsys Servicios de Procesamiento, S.L., CIF B85955367
*/
namespace Redsys\Redsys\Block;

use Magento\Customer\Model\Context;
use Magento\Sales\Model\Order;

class Notify extends \Magento\Framework\View\Element\Template
{
	protected $_order;

	protected $_checkoutSession;

	protected $customerSession;

	protected $_storeManager;

	protected $_orderConfig;

	protected $httpContext;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Sales\Model\Order $order,
		\Magento\Sales\Model\Order\Config $orderConfig,
		\Magento\Framework\App\Http\Context $httpContext,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Magento\Customer\Model\Session $customerSession,
		array $data = []
	){
		parent::__construct($context, $data);
		$this->_order = $order;
		$this->checkoutSession = $checkoutSession;
		$this->_orderConfig = $orderConfig;
		$this->_orderCollectionFactory = $orderCollectionFactory;
		$this->httpContext = $httpContext;
		$this->_storeManager = $storeManager;
		$this->_customerSession = $customerSession;
	}

    public function _prepareLayout()
    {
    	$this->prepareBlockData();
        return parent::_prepareLayout();
    }

    protected function prepareBlockData()
    {
        $orders = $this->getOrders();
        foreach ($orders as $key => $order) {
	        	$this->addData(
	            [
	                'is_order_visible' => true,
	                'view_order_url' => $this->getUrl(
	                    'sales/order/view/',
	                    ['order_id' => $order->getEntityId()]
	                ),
	                'print_url' => $this->getUrl(
	                    'sales/order/print',
	                    ['order_id' => $order->getEntityId()]
	                ),
	                'can_print_order' => true,
	                'can_view_order'  => true,
	                'order_id'  => $order->getIncrementId()
	            ]
	        );
        	break;
        }
        
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */

    public function getOrders()
    {
        $customerId = $this->_customerSession->getCustomerId(); 

        $orders = $this->_orderCollectionFactory->create($customerId)->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'status',
            ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
        )->setOrder(
            'created_at',
            'desc'
        );
       
        return $orders;
    }

    public function getContinueUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}