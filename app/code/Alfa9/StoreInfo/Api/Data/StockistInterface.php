<?php

namespace Alfa9\StoreInfo\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface StockistInterface extends ExtensibleDataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */

    const SR_ID               = 'id_sr';
    const STOCKIST_ID         = 'storeinfo_id';
    const NAME                = 'name';
    const ADDRESS             = 'address';
    const CITY                = 'city';
    const POSTCODE            = 'postcode';
    const REGION              = 'region';
    const EMAIL               = 'email';
    const EMAIL_ORDER         = 'email_order';
    const PHONE               = 'phone';
    const LATITUDE            = 'latitude';
    const LONGITUDE           = 'longitude';
    const LINK                = 'link';
    const STATUS              = 'status';
    const TYPE                = 'type';
    const COUNTRY             = 'country';
    const IMAGE               = 'image';
    const IMAGE2              = 'image2';
    const IMAGE3              = 'image3';
    const CREATED_AT          = 'created_at';
    const UPDATED_AT          = 'updated_at';
    const STORE_ID            = 'store_id';
    const SCHEDULE            = 'schedule';
    const INTRO               = 'intro';
    const DESCRIPTION         = 'description';
    const SERVICES            = 'services';
    const DISTANCE            = 'distance';
    const STATION             = 'station';
    const DETAILS_IMAGE       = 'details_image';
    const EXTERNAL_LINK       = 'external_link';
    const SHIPPING_TIME       = 'shipping_time';
    const AVAILABILITY        = 'availability';
    /**
     * Get schedule
     *
     * @return string
     */
    public function getSchedule();


    /**
     * Get intro
     *
     * @return string
     */
    public function getIntro();

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get services
     *
     * @return string
     */
    public function getServices();

    /**
     * Get external link
     *
     * @return string
     */
    public function getExternalLink();

    /**
     * Get distance
     *
     * @return string
     */
    public function getDistance();

    /**
     * Get station
     *
     * @return string
     */
    public function getStation();

    /**
     * Get store details image
     *
     * @return string
     */
    public function getDetailsImage();


    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Get store url
     *
     * @return string
     */
    public function getLink();
    
    /**
     * Get address
     *
     * @return string
     */
    public function getAddress();
    
    /**
     * Get city
     *
     * @return string
     */
    public function getCity();
    
    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode();
    
    /**
     * Get region
     *
     * @return string
     */
    public function getRegion();
    
    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Get email
     *
     * @return string
     */
    public function getEmailOrder();

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone();
    
    /**
     * Get image
     *
     * @return string
     */
    public function getImage();

    /**
     * Get image
     *
     * @return string
     */
    public function getImage2();

    /**
     * Get image
     *
     * @return string
     */
    public function getImage3();
    
    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude();
    
    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude();

    /**
     * Get is active
     *
     * @return bool|int
     */
    public function getStatus();

    /**
     * Get type
     *
     * @return int
     */
    public function getType();

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry();


    /**
     * set name
     *
     * @param $name
     * @return StockistInterface
     */
    public function setName($name);

    /**
     * set link
     *
     * @param $link
     * @return StockistInterface
     */
    public function setLink($link);
    
    /**
     * set image
     *
     * @param $image
     * @return StockistInterface
     */
    public function setImage($image);

    /**
     * set image
     *
     * @param $image2
     * @return StockistInterface
     */
    public function setImage2($image2);

    /**
     * set image
     *
     * @param $image3
     * @return StockistInterface
     */
    public function setImage3($image3);
    
    /**
     * set address
     *
     * @param $address
     * @return StockistInterface
     */
    public function setAddress($address);

    /**
     * set city
     *
     * @param $city
     * @return StockistInterface
     */
    public function setCity($city);
    
    /**
     * set postcode
     *
     * @param $postcode
     * @return StockistInterface
     */
    public function setPostcode($postcode);


    /**
     * set schedule
     *
     * @param $schedule
     * @return StockistInterface
     */
    public function setSchedule($schedule);

    /**
     * set description
     *
     * @param $description
     * @return StockistInterface
     */
    public function setDescription($description);

    /**
     * set services
     *
     * @param $services
     * @return StockistInterface
     */
    public function setServices($services);

    /**
     * set distance
     *
     * @param $distance
     * @return StockistInterface
     */
    public function setDistance($distance);

    /**
     * set station
     *
     * @param $station
     * @return StockistInterface
     */
    public function setStation($station);

    /**
     * set external link
     *
     * @param $external_link
     * @return StockistInterface
     */
    public function setExternalLink($external_link);

    /**
     * set intro
     *
     * @param $intro
     * @return StockistInterface
     */
    public function setIntro($intro);

    /**
     * set store details image
     *
     * @param $details_image
     * @return StockistInterface
     */
    public function setDetailsImage($details_image);

    /**
     * set region
     *
     * @param $region
     * @return StockistInterface
     */
    public function setRegion($region);

    /**
     * set email
     *
     * @param $email
     * @return StockistInterface
     */
    public function setEmail($email);

    /**
     * set email
     *
     * @param $email
     * @return StockistInterface
     */
    public function setEmailOrder($email);
    /**
     * set phone
     *
     * @param $phone
     * @return StockistInterface
     */
    public function setPhone($phone);

    /**
     * set latitude
     *
     * @param $latitude
     * @return StockistInterface
     */
    public function setLatitude($latitude);
    
    /**
     * set longitude
     *
     * @param $longitude
     * @return StockistInterface
     */
    public function setLongitude($longitude);

    /**
     * Set status
     *
     * @param $status
     * @return StockistInterface
     */
    public function setStatus($status);

    /**
     * set type
     *
     * @param $type
     * @return StockistInterface
     */
    public function setType($type);

    /**
     * Set country
     *
     * @param $country
     * @return StockistInterface
     */
    public function setCountry($country);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * set created at
     *
     * @param $createdAt
     * @return StockistInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return StockistInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @param $storeId
     * @return StockistInterface
     */
    public function setStoreId($storeId);

    /**
     * @return int[]
     */
    public function getStoreId();

    /**
     * @param $srId
     * @return StockistInterface
     */
    public function setIdSr($srId);

    /**
     * @return int
     */
    public function getIdSr();

    /**
     * Get shipping_time
     *
     * @return string
     */
    public function getShippingTime();

    /**
     * @param $shippingTime
     * @return StockistInterface
     */
    public function setShippingTime($shippingTime);

    /**
     * @return string
     */
    public function getAvailability();

    /**
     * @param string $availability
     * @return StockistInterface
     */
    public function setAvailability($availability);
}
