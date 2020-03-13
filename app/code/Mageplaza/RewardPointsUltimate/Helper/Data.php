<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Helper;

use Magento\Bundle\Model\Product\Type;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\System\Store;
use Mageplaza\RewardPoints\Helper\Data as RewardHelper;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;
use Mageplaza\RewardPointsUltimate\Model\Source\UrlParam;

/**
 * Class Data
 * @package Mageplaza\RewardPointsUltimate\Helper
 */
class Data extends RewardHelper
{
    const BEHAVIOR_CONFIGURATION  = '/behavior';
    const REFERRALS_CONFIGURATION = '/referrals';
    const DEFAULT_URL_PREFIX      = 'code';

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $resolver;

    /**
     * Transaction Action Code
     */
    const ACTION_SIGN_UP              = 'earning_sign_up';
    const ACTION_NEWSLETTER           = 'earning_newsletter_subscriber';
    const ACTION_REVIEW_PRODUCT       = 'earning_review_product';
    const ACTION_CUSTOMER_BIRTHDAY    = 'earning_customer_birthday';
    const ACTION_SEND_EMAIL_TO_FRIEND = 'earning_send_email_to_friend';
    const ACTION_LIKE_FACEBOOK        = 'earning_like_facebook';
    const ACTION_UNLIKE_FACEBOOK      = 'earning_unlike_facebook';
    const ACTION_TWEET_TWITTER        = 'earning_tweet_twitter';
    const ACTION_SHARE_FACEBOOK       = 'earning_share_facebook';
    const ACTION_SHARE_GOOGLE_PLUS    = 'earning_share_google_plus';
    const ACTION_SELL_POINTS          = 'sell_points_order';
    const ACTION_SELL_POINTS_REFUND   = 'sell_points_order_refund';
    const ACTION_REFERRAL_EARNING     = 'referral_earning';
    const ACTION_REFERRAL_REFUND      = 'referral_refund';
    const ACTION_IMPORT_TRANSACTION   = 'import_transaction';

    /**
     * @var \Mageplaza\RewardPointsUltimate\Model\BehaviorFactory
     *
     */
    protected $behaviorFactory;

    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * @var array
     */
    protected $storesOptionHash = [];

    /**
     * @var array
     */
    protected $websitesOptionHash = [];

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $timeZone
     * @param BehaviorFactory $behaviorFactory
     * @param Resolver $resolver
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $timeZone,
        BehaviorFactory $behaviorFactory,
        Resolver $resolver,
        Store $store
    ) {
        $this->behaviorFactory = $behaviorFactory;
        $this->resolver = $resolver;
        $this->systemStore = $store;

        parent::__construct($context, $objectManager, $storeManager, $priceCurrency, $timeZone);
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getConfigBehavior($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . self::BEHAVIOR_CONFIGURATION . $code, $storeId);
    }

    /**
     * @param $type
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEnabledSocialButton($type, $storeId = null)
    {
        return $this->getConfigBehavior($type . '/enabled', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getFacebookButtonCount($storeId = null)
    {
        return $this->getConfigBehavior('facebook/show_count', $storeId);
    }

    /**
     * @param $type
     * @param null $storeId
     *
     * @return mixed
     */
    public function getSocialPageDisplay($type, $storeId = null)
    {
        return $this->getConfigBehavior($type . '/pages_display', $storeId);
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getConfigReferrals($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . self::REFERRALS_CONFIGURATION . $code, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getInvitationEmail($storeId = null)
    {
        return $this->getConfigReferrals('general/email', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getDefaultReferUrl($storeId = null)
    {
        return $this->getConfigReferrals('general/default_url', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getURLParam($storeId = null)
    {
        return $this->getConfigReferrals('url_key/param', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getURLPrefix($storeId = null)
    {
        return $this->getConfigReferrals('url_key/prefix', $storeId);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentUrl()
    {
        return $this->storeManager->getStore()->getCurrentUrl(false);
    }

    /***
     * @return array
     */
    public function getStores()
    {
        if (!$this->storesOptionHash) {
            $this->storesOptionHash = $this->systemStore->getStoreOptionHash();
        }

        return $this->storesOptionHash;
    }

    /***
     * @return array
     */
    public function getWebsites()
    {
        if (!$this->websitesOptionHash) {
            $this->websitesOptionHash = $this->systemStore->getWebsiteOptionHash();
        }

        return $this->websitesOptionHash;
    }

    /**
     * @return bool
     */
    public function canUseStoreSwitcherLayoutByMpReports()
    {
        if ($this->isModuleOutputEnabled('Mageplaza_Reports')) {
            $mpReportModule = $this->objectManager->create('\Mageplaza\Reports\Helper\Data');

            return $mpReportModule->isEnabled();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isDisabledFilters()
    {
        return !$this->canUseStoreSwitcherLayoutByMpReports();
    }

    /**
     * @param $result
     * @param $query
     * @param $customHtml
     *
     * @return string
     */
    public function changeHtmlWithDOM($result, $query, $customHtml)
    {
        $dom = new \DOMDocument();
        $result = mb_convert_encoding($result, 'HTML-ENTITIES', 'utf-8');
        $dom->loadHTML($result);
        $xpath = new \DOMXpath($dom);
        $query = $xpath->query($query);
        if ($query->length > 0) {
            $template = $dom->createDocumentFragment();
            $template->appendXML($customHtml);
            $query->item(0)->appendChild($template);
            $result = $dom->saveHTML();
        }

        return $result;
    }

    /**
     * @param $action
     * @param array $changeHtml
     *
     * @return string
     */
    public function getPointHtml($action, $changeHtml = [])
    {
        $pointAction = $this->behaviorFactory->create()->getPointByAction($action);
        $html = '';
        if ($this->isEnabled() && $pointAction > 0) {
            $pointHelper = $this->getPointHelper();
            $html = '<div class="mp-reward-earning" style="margin: 5px 0 5px 0">';
            $html .= $pointHelper->getIconHtml();
            $html .= '<span style="margin-left: 5px">' . $this->replaceMessage(
                $action,
                '<strong>' . $pointHelper->format($pointAction) . '</strong>'
            ) . '</span>';
            $html .= '</div>';
            if (isset($changeHtml['result']) && $changeHtml['query']) {
                $html = $this->changeHtmlWithDOM($changeHtml['result'], $changeHtml['query'], $html);
            }
        }

        return $html;
    }

    /**
     * @param array $changeHtml
     *
     * @return string
     */
    public function getSubscribePointHtml($changeHtml = [])
    {
        return $this->getPointHtml(CustomerEvents::NEWSLETTER, $changeHtml);
    }

    /**
     * @return string
     */
    public function getProductReviewPointHtml()
    {
        return $this->getPointHtml(CustomerEvents::PRODUCT_REVIEW);
    }

    /**
     * @param $action
     * @param $point
     *
     * @return mixed
     */
    public function replaceMessage($action, $point)
    {
        $messages = [
            CustomerEvents::NEWSLETTER        => __('Earn %1 for subscribing to newsletter', $point),
            CustomerEvents::PRODUCT_REVIEW    => __('Earn %1 for writing a review for this product', $point),
            CustomerEvents::SIGN_UP           => __('Earn %1 for registering an account', $point),
            CustomerEvents::CUSTOMER_BIRTHDAY => __('Earn %1 on your birthday', $point)
        ];
        if (isset($messages[$action])) {
            return $messages[$action];
        }

        return '';
    }

    /**
     * @param $filters
     * @param bool $orderBy
     * @param bool $isFirstItem
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getTransactionByFieldToFilter($filters, $orderBy = false, $isFirstItem = false)
    {
        $transactions = $this->getTransaction()->getCollection();
        foreach ($filters as $field => $value) {
            $transactions->addFieldToFilter($field, $value);
        }
        if ($orderBy) {
            $transactions->setOrder('transaction_id', $orderBy);
        }
        if ($isFirstItem) {
            $transactions->getFirstItem();
        }

        return $transactions;
    }

    /**
     * @param $filters
     * @param bool $orderBy
     * @param bool $isFirstItem
     * @param array $conditions
     * @param bool $isGetPointAmount
     *
     * @return int
     */
    public function getTransactionByFilter(
        $filters,
        $orderBy = false,
        $isFirstItem = false,
        $conditions = [],
        $isGetPointAmount = false
    ) {
        $pointAmount = 0;
        $transactions = $this->getTransactionByFieldToFilter($filters, $orderBy, $isFirstItem);
        foreach ($transactions as $transaction) {
            $extraContent = $this->getExtraContent($transaction);
            $extraContentCondition = $extraContent[$conditions['field']];
            if (isset($extraContentCondition) && $extraContentCondition == $conditions['value'] && !$isGetPointAmount) {
                return $transaction;
            }
            $pointAmount += $transaction->getPointAmount();
        }

        return $pointAmount;
    }

    /**
     * @param $transaction
     *
     * @return array|mixed
     */
    public function getExtraContent($transaction)
    {
        if ($transaction->getExtraContent()) {
            return self::jsonDecode($transaction->getExtraContent());
        }

        return [];
    }

    /**
     * @param $url
     * @param $shareUrl
     *
     * @return string
     */
    public function getTwitterButton($url, $shareUrl, $isBindEvent = true)
    {
        $html
            = '<div class="mp-rw-social twitter-earning" style ="float:left;  margin:5px;">
                     <a href="https://twitter.com/share" class="twitter-share-button"
                        data-lang="en"
                        data-url="' . $shareUrl . '">' . __('Tweet') . ' </a>
                </div>';
        if ($isBindEvent) {
            $html
                .= '<script>
                            twttr.ready(function (twttr) {
                                twttr.events.bind("click", function (event) {
                                    mpSocials.sendAjax("' . $url . '",event.target.dataset.url);
                                });
                            });
                        </script>';
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getTwitterScript()
    {
        return '
            <script>
                    //<![CDATA[
                        window.twttr = (function (d, s, id) {
                            var t, js, fjs = d.getElementsByTagName(s)[0];
                            if (d.getElementById(id)) return;
                            js = d.createElement(s);
                            js.id = id;
                            js.src = "https://platform.twitter.com/widgets.js";
                            fjs.parentNode.insertBefore(js, fjs);
                            return window.twttr || (t = {_e: [], ready: function (f) {
                                t._e.push(f)
                            }});
                        }(document, "script", "twitter-wjs"));
                    //]]>
            </script>
        ';
    }

    /**
     * @param string $appId
     *
     * @return string
     */
    public function getFacebookScript($appId = '')
    {
        return '<script>
                //<![CDATA[
                (function (d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s);
                    js.id = id;
                    js.src = "//connect.facebook.net/' . $this->resolver->getLocale() . '/sdk.js#xfbml=1&version=v2.0' . $appId . '";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, "script", "facebook-jssdk"));
                //]]>
            </script>';
    }

    /**
     * @return Cookie
     */
    public function getCookieHelper()
    {
        return $this->objectManager->get(Cookie::class);
    }

    /**
     * @return mixed
     */
    public function getCryptHelper()
    {
        return $this->objectManager->get(Crypt::class);
    }

    /**
     * @param $code
     *
     * @return string
     */
    public function getReferUrl($code)
    {
        $prefix = $this->getURLPrefix() ?: self::DEFAULT_URL_PREFIX;
        $urlParam = "?$prefix=" . $code;
        if ($this->getURLParam() == UrlParam::HASH) {
            $urlParam = "#" . $prefix . $code;
        }

        return $this->_urlBuilder->getUrl() . $urlParam;
    }

    /**
     * @param $order
     * @param $object
     * @param $action
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function calculateReferralPoints($order, $object, $action)
    {
        $referralEarn = 0;
        $id = $object->getOrigData('entity_id');
        if (is_null($id)) {
            foreach ($object->getItems() as $item) {
                $orderItem = $item->getOrderItem();
                $mpReferralEarn = $orderItem->getMpRewardReferralEarn();
                if ($orderItem->getProductType() == Type::TYPE_CODE || !$mpReferralEarn) {
                    continue;
                }
                $referralEarn += ($mpReferralEarn * $item->getQty()) / $orderItem->getQtyOrdered();
            }
        }

        if ($object instanceof \Magento\Sales\Model\Order\Creditmemo) {
            $referralEarn = -$referralEarn;
        }

        if ($referralEarn) {
            $this->addTransaction(
                $action,
                $order->getMpRewardReferralId(),
                $referralEarn,
                $order
            );
        }
    }

    /**
     * @param $searchCriteria
     * @param $searchResult
     *
     * @return mixed
     */
    public function processGetList($searchCriteria, $searchResult)
    {
        if ($this->versionCompare('2.2.0')) {
            $collectionProcessor = $this->objectManager->get('\Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface');
            $joinProcessor = $this->objectManager->get('\Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface');
            $collectionProcessor->process($searchCriteria, $searchResult);
            $joinProcessor->process($searchResult);
        } else {
            foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
                $this->addFilterGroupToCollection($filterGroup, $searchResult);
            }

            $sortOrders = $searchCriteria->getSortOrders();
            if ($sortOrders === null) {
                $sortOrders = [];
            }
            /** @var \Magento\Framework\Api\SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $field = $sortOrder->getField();
                $searchResult->addOrder(
                    $field,
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }

            $searchResult->setCurPage($searchCriteria->getCurrentPage());
            $searchResult->setPageSize($searchCriteria->getPageSize());
        }

        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }

    /**
     * @param $filterGroup
     * @param $searchResult
     */
    protected function addFilterGroupToCollection($filterGroup, $searchResult)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $conditions[] = [$condition => $filter->getValue()];
            $fields[] = $filter->getField();
        }
        if ($fields) {
            $searchResult->addFieldToFilter($fields, $conditions);
        }
    }
}
