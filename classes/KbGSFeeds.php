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
class KbGSFeeds extends ObjectModel
{
    public $id_gs_feed;
    public $id_gs_profiles;
    public $upload_type;
    public $feed_label;
    public $upload_hour;
    public $day_of_month;
    public $weekday;
    public $active;
    public $date_add;
    public $date_upd;
    public $id_shop;
    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'kb_gs_feeds',
        'primary' => 'id_gs_feed',
        'fields' => array(
            'id_gs_feed' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'id_gs_profiles' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'feed_label' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'upload_type' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'day_of_month' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'weekday' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'upload_hour' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName'
            ),
            'active' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
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
    
    //function to all feeds
    public static function getKbFeeds()
    {
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
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
        return Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'kb_gs_feeds' . ' WHERE id_shop = ' . (int)$shop_id);
    }
    
    //function to get feed based on profile
    public static function getFeedByProfileId($id_gs_profiles)
    {
        if (empty($id_gs_profiles)) {
            return;
        }
	/**
	* To make the module multi-store compatible
	* @Author Ravi Kant Gupta
	* @date 12-11-2024
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
                return Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'kb_gs_feeds WHERE id_gs_profiles='.(int)$id_gs_profiles . ' AND id_shop = ' . (int)$shop_id);
    }
}
