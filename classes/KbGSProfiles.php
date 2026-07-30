<?php
/**
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future.If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
* We offer the best and most useful modules PrestaShop and modifications for your online store.
*
* @author    knowband.com <support@knowband.com>
* @copyright 2017 Knowband
* @license   see file: LICENSE.txt
* @category  PrestaShop Module
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

//Class and its methods to handle
class KbGSProfiles extends ObjectModel
{
    public $id_gs_profiles;
    public $profile_title;
    public $gtin;
    public $id_country;
    public $id_currency;
    public $id_lang;
//    public
//    public $gs_category_code;
//    public $store_categories;
    
//    public $id_etsy_shipping_templates;
//    public $-is_customizable;
//    public $who_made;
//    public $when_made;
//    public $is_supply;
//    public $recipient;
//    public $occassion;
    /*Start- MK made changes on 22-11-2017 to add field in model*/
    public $customize_product_title;
    public $shipping;
    public $custom_label_0;
    public $custom_label_1;
    public $custom_label_2;
    public $custom_label_3;
    public $custom_label_4;
	 /**
	 * @Author Ravi Kant Gupta
	 * @date 17-09-2024
	 * To add the data in the profile table
	 */  
    public $listing_type;
    public $product_type;
    public $product_condition;
    public $promotion_id;
//    public $enable_max_qty;
//    public $max_qty;
//    public $enable_min_qty;
//    public $min_qty;
//    public $property;
    /*End- MK made changes on 22-11-2017 to add field in model*/
    public $active;
    public $feed_generated;
    public $date_add;
    public $date_upd;
    public $id_shop;
    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'kb_gs_profiles',
        'primary' => 'id_gs_profiles',
        'fields' => array(
            'id_gs_profiles' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'profile_title' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'gtin' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            /**
             * To add new fields in profile table 
             * @date 17-09-2024 
             * @author Ravi Kant Gupta
             */
            'listing_type' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml'
            ),
            'promotion_id' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml'
            ),
            'product_type' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml'
            ),
            'product_condition' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml'
            ),
            'id_country' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'id_currency' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'id_lang' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'customize_product_title' => array(
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml'
            ),
            'shipping' => array(
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml'
            ),
            'custom_label_0' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml'
            ),
            'custom_label_1' => array(
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml'
            ),
            'custom_label_2' => array(
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml'
            ),
            'custom_label_3' => array(
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml'
            ),
            'custom_label_4' => array(
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml'
            ),
            'active' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'feed_generated' => array(
                'type' => self::TYPE_HTML,
                'validate' => 'isCleanHtml'
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'copy_post' => false
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'copy_post' => false
            ),
           'id_shop' => array(
               'type' => self::TYPE_INT,
               'validate' => 'isInt'
           ),
        ),
    );
    
    public function __construct($id = null)
    {
        parent::__construct($id);
    }
    
    //function to get all google country
    public static function getGSCountries()
    {
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $data = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'kb_gs_countries WHERE id_shop = ' . (int)$shop_id);
        return $data;
    }
    
    //function to get language based on country
    public static function getGSLanguageByCountryID($id_country)
    {
        if (empty($id_country)) {
            return;
        }
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $data = Db::getInstance()->executeS('SELECT l.* FROM '._DB_PREFIX_.'kb_gs_languages l INNER JOIN '._DB_PREFIX_.'kb_gs_countries c on (l.id_gs_countries=c.id_gs_countries) Where c.id_country='.(int)$id_country . ' AND l.id_shop = ' . (int)$shop_id);
        return $data;
    }
    
    //function to get country based on iso code
    public static function getGSCountryByISO($iso_code)
    {
        if (empty($iso_code)) {
            return;
        }
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $data = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'kb_gs_countries where iso_code="'.pSQL($iso_code).'"' . ' AND id_shop = ' . (int)$shop_id);
        return $data;
    }
    
    //function to get currency based on country id
    public static function getGSCurrencyByCountryID($id_country)
    {
        if (empty($id_country)) {
            return;
        }
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;
        $data = Db::getInstance()->executeS('SELECT l.* FROM '._DB_PREFIX_.'kb_gs_currencies l INNER JOIN '._DB_PREFIX_.'kb_gs_countries c on (l.id_gs_countries=c.id_gs_countries) Where c.id_country='.(int)$id_country . ' AND l.id_shop = ' . (int)$shop_id);
        return $data;
    }
    
    //function to get all active profile
    public static function getAllProfile()
    {
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 11-11-2024
	* Added check for All store and default group selection, if the shop_id is null then use shop_id =1
	*/
        $shop_id = (int)Shop::getContextShopID();
        if($shop_id == null){
            $shop_id = 1;
        } 
        /**
             * Shop id was not changing when we switch between stores, so assigned shop_id from the context
             * @modifier Himanshu Vishwakarma
             * @date 05-04-2025
             */
            $shop_id = (int)Context::getContext()->shop->id;

        $data = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'kb_gs_profiles Where active=1' . ' AND id_shop = ' .(int)$shop_id);
        return $data;
    }
}
