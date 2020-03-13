<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const COOKIE_NAME = 'mdirector';
    /**
     * PATH Names
     */
    const CONFIG_ENABLED = 'm_director/general/enabled';
    const CONFIG_CONSUMER_KEY = 'm_director/general/consumer_key';
    const CONFIG_CONSUMER_SECRET = 'm_director/general/consumer_secret';
    const CONFIG_BASE_URL = 'm_director/general/base_url';
    const CONFIG_MAP_CONTACT_ATTRIBUTE = 'm_director/general/map_contact_attribute';
    const CONFIG_MAP_LIST_CUSTOMER_GROUP = 'm_director/general/map_list_per_customer_group';
    const CONFIG_DEFAULT_LIST = 'm_director/general/default_list';
    const CONFIG_SUBSCRIPTION_SEND_WELCOME_EMAIL  = 'm_director/subscription/enabled';
    const CONFIG_UN_SUBSCRIPTION_ENABLED = 'm_director/unsubscription_sync/enabled';
    const CONFIG_UN_SUBSCRIPTION_LAST_TIME = 'm_director/unsubscription_sync/last_date';
    const CONFIG_PIXEL_ENABLED = 'm_director/pixel/enabled';
    const CONFIG_PIXEL_REG_EXP = 'm_director/pixel/regexp';
    /**
     * @var \Alfa9\MDirector\Model\Api\Contact
     */
    private $apiContact;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    /**
     * @var \Alfa9\MDirector\Model\Api\UnSubscription
     */
    private $apiUnSubscription;
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;
    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Alfa9\MDirector\Model\Api\Contact $apiContact
     * @param \Alfa9\MDirector\Model\Api\UnSubscription $apiUnSubscription
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Alfa9\MDirector\Model\Api\Contact $apiContact,
        \Alfa9\MDirector\Model\Api\UnSubscription $apiUnSubscription,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ){
        $this->customerFactory = $customerFactory;
        $this->apiContact = $apiContact;
        $this->apiUnSubscription = $apiUnSubscription;
        $this->cookieManager = $cookieManager;
        $this->productMetadata = $productMetadata;
        parent::__construct($context);
    }

    /**
     * Get the version of Magento
     * @return string
     */
    public function getMagentoVersion(){
        return $this->productMetadata->getVersion();
    }
    /**
     * @param string $configPath
     * @return string
     */
    private function getConfigValue($configPath) {
        return $this->scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORES);
    }
    /**
     * Check if the Module Is Enabled
     * @return bool
     */
    public function getIsEnabled(){
        return $this->getConfigValue(self::CONFIG_ENABLED);
    }

    /**
     * Check if the Un Subscription is Enabled
     * @return bool
     */
    public function isUnSubscriptionEnabled(){
        return $this->getIsEnabled() && $this->getConfigValue(self::CONFIG_UN_SUBSCRIPTION_ENABLED);
    }

    /**
     * Get the last time of the un subscription
     * @return string
     */
    public function unSubscriptionLastTime() {
        return $this->getConfigValue(self::CONFIG_UN_SUBSCRIPTION_LAST_TIME);
    }
    /**
     * Return the serialize data from the config
     * @return array
     */
    public function getMapContactAttribute() {
        return $this->getDataFromSerializable($this->getConfigValue(self::CONFIG_MAP_CONTACT_ATTRIBUTE));
    }

    /**
     * @param $text
     * @return array
     */
    private function getDataFromSerializable($text){
        $magentoVersion = $this->getMagentoVersion();
        if(strpos($magentoVersion, '2.2') === false) {
            return unserialize($text);
        } else {
            return json_decode($text, true);
        }
    }
    /**
     * Get the List of the Group
     * @return array
     */
    public function getMapListCustomerGroup() {
        return $this->getDataFromSerializable($this->getConfigValue(self::CONFIG_MAP_LIST_CUSTOMER_GROUP));
    }

    /**
     * Get the Default List
     * @return int
     */
    public function getDefaultList() {
        return (int)$this->getConfigValue(self::CONFIG_DEFAULT_LIST);
    }
    /**
     * Check if can send mdirector welcome email
     * @return bool
     */
    public function sendMDirectorWelcomeEmail()
    {
        return (bool)$this->getConfigValue(self::CONFIG_SUBSCRIPTION_SEND_WELCOME_EMAIL);
    }

    /**
     * Check if the Pixel is enabled
     * @return bool
     */
    public function isPixelEnabled()
    {
        return (bool)$this->getConfigValue(self::CONFIG_PIXEL_ENABLED);
    }

    /**
     * Return the pixel regular expression
     * @return string
     */
    public function getPixelRegularExpression()
    {
        return $this->getConfigValue(self::CONFIG_PIXEL_REG_EXP);
    }
    /**
     * Return ListId
     *
     * @param int $customerGroupId
     * @return int
     */
    public function getListIdByCustomerGroupId($customerGroupId)
    {
        $listPerCustomerGroup = $this->getMapListCustomerGroup();
        foreach ($listPerCustomerGroup as $list) {
            if ($list['customer_group'] == $customerGroupId) {
                return $list['list'];
            }
        }
        return $this->getDefaultList();
    }
    /**
     * Subscribe to list
     *
     * @param string $email
     * @param \Magento\Customer\Model\Customer|\Magento\Customer\Api\Data\CustomerInterface|int|string $customer
     * @param int $listId
     * @return bool
     */
    public function subscribe($email, $customer, $listId = null)
    {
        if (is_numeric($customer)) {
            try {
                $customer =  $this->customerFactory->create()->load($customer);
            }catch (\Exception $exception) {
                return false;
            }
        }
        $customerGroupId = $customer->getId() ? $customer->getGroupId() : \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID;
        $listId = $listId ? $listId : $this->getListIdByCustomerGroupId($customerGroupId);
        $sendWelcomeEmail = $this->sendMDirectorWelcomeEmail();
        $data = $this->getContactData($customer);
        $data = array_merge($data, [
            'listId'                => $listId,
            'email'                 => $email,
            'send-notifications'    => (int)$sendWelcomeEmail,
        ]);

        $this->apiUnSubscription->delete($data); // Need remove from MDirector unsubscription list before subscribe
        $result = $this->apiContact->create($data);
        if ($result && isset($result['response']) && $result['response'] == 'ok') {
            return true;
        }
        return false;
    }
    /**
     * Update Subscriber
     *
     * @param \Magento\Customer\Model\Customer|\Magento\Customer\Api\Data\CustomerInterface|int|string $customer
     * @param int $listId
     * @return bool
     */
    public function updateSubscriber($customer, $listId)
    {
        if (is_numeric($customer)) {
            try {
                $customer =  $this->customerFactory->create()->load($customer);
            }catch (\Exception $exception) {
                return false;
            }
        }
        $data = [
            'email'  => $customer->getEmail(),
            'listId' => (int)$listId
        ];
        $contact = $this->apiContact->fetch($data);
        if ($contact && $contact['response'] == 'ok') {
            $data['conId'] = $contact['data']['conId'];
            $data = array_merge($data, $this->getContactData($customer));
            $result = $this->apiContact->update($data);
            if ($result && $result['response'] == 'ok') {
                return true;
            }
        }
        return false;
    }
    /**
     * Unsubscribe
     *
     * @param string $email
     * @return bool
     */
    public function unsubscribe($email)
    {
        $data = [
            'email'       => $email,
            'unsubscribe' => true,
            'reason'      => __('Unsubscription')
        ];
        $result = $this->apiContact->delete($data);
        if ($result && $result['response'] == 'ok') {
            return true;
        }
        return false;
    }
    /**
     * Remove contact from list
     *
     * @param string $email
     * @param int $listId
     * @return bool
     */
    public function unsubscribeFromList($email, $listId)
    {
        $data = [
            'email'         => $email,
            'listId'        => $listId
        ];
        $contact = $this->apiContact->fetch($data);
        if ($contact && $contact['response'] == 'ok') {
            $data = array(
                'conId'         => $contact['data']['conId'],
                'listId'        => $listId,
                'unsubscribe'   => false,
                'reason'        => __('Unsubscription from List'),
            );
            $result = $this->apiContact->delete($data);
            if ($result && $result['response'] == 'ok') {
                return true;
            }
        }
        return false;
    }
    /**
     * Extract Contact Data from Customer
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return array
     */
    public function getContactData($customer)
    {
        $data = [];
        $customerAddress = $customer->getDefaultBillingAddress();

        $attributes = $this->getMapContactAttribute();
        foreach ($attributes as $attribute) {
            if(!isset($attribute['contact_attribute']) || !isset($attribute['customer_attribute'])) {
                continue;
            }
            $key = $attribute['contact_attribute'];
            switch ($attribute['customer_attribute']) {
                case 'street':
                    $data[$key] = !$customerAddress? '': trim($customerAddress->getStreetFull());
                    break;
                case 'region':
                    $data[$key] = !$customerAddress? '':$customerAddress->getRegion();
                    break;
                case 'country':
                    $data[$key] = !$customerAddress? '': $customerAddress->getCountry();
                    break;
                case 'company':
                case 'city':
                case 'postcode':
                case 'telephone':
                case 'fax':
                    $data[$key] = !$customerAddress? '': $customerAddress->getData($attribute['customer_attribute']);
                    break;
                default:
                    $data[$key] = $customer->getData($attribute['customer_attribute']);
                    break;
            }
        }
        return $data;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public function findByDateUnSubscription($startDate, $endDate){
        try {
            $result =  $this->apiUnSubscription->findByDate($startDate, $endDate);
        }catch (\Exception $exception) {
            $result = [];
        }
        return $result;
    }

    /**
     * Return mdirector dataId tracked
     *
     * @return string
     */
    public function getDataId()
    {
        return $this->cookieManager->getCookie(self::COOKIE_NAME);
    }
}